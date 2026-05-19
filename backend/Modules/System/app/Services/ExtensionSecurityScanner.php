<?php

declare(strict_types=1);

namespace Modules\System\Services;

use Exception;
use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\ShellExec;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ZipArchive;

class ExtensionSecurityScanner
{
    /**
     * List of banned system and dangerous functions.
     */
    protected array $bannedFunctions = [
        'exec',
        'shell_exec',
        'system',
        'passthru',
        'popen',
        'proc_open',
        'pcntl_exec',
        'assert',
        'create_function',
    ];

    /**
     * Scan all PHP files inside a ZIP archive using the AST parser.
     */
    public function scanZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Gagal membuka file paket ZIP.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (pathinfo($filename, PATHINFO_EXTENSION) === 'php') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    $this->scanCode($content, $filename);
                }
            }
        }

        $zip->close();
    }

    /**
     * Scan all PHP files inside a directory recursively using the AST parser.
     */
    public function scanDirectory(string $dirPath): void
    {
        if (! is_dir($dirPath)) {
            throw new Exception("Direktori tidak ditemukan: {$dirPath}");
        }

        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getRealPath());
                $this->scanCode($content, $file->getRelativePathname());
            }
        }
    }

    /**
     * Parse and traverse a single PHP code string to check for AST violations.
     */
    public function scanCode(string $code, string $filePath = 'unknown'): void
    {
        try {
            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $stmts = $parser->parse($code);

            if ($stmts === null) {
                return;
            }

            $traverser = new NodeTraverser();
            $visitor = new class($this->bannedFunctions, $filePath) extends NodeVisitorAbstract {
                public array $violations = [];

                public function __construct(
                    protected array $bannedFunctions,
                    protected string $filePath
                ) {}

                public function enterNode(Node $node)
                {
                    // 1. Detect 'eval()' construct
                    if ($node instanceof Eval_) {
                        $this->violations[] = "Security Gate Violation: Penggunaan konstruksi berbahaya 'eval()' terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                    }

                    // 2. Detect shell backtick operators (e.g. `ls -la`)
                    if ($node instanceof ShellExec) {
                        $this->violations[] = "Security Gate Violation: Penggunaan operator backtick shell terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                    }

                    // 3. Detect system execution function calls
                    if ($node instanceof FuncCall) {
                        // Dynamic function invocation (e.g., $func())
                        if ($node->name instanceof Expr) {
                            $this->violations[] = "Security Gate Violation: Eksekusi fungsi dinamis terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}. Pola ini dilarang untuk mencegah penyuntingan kode tersembunyi.";
                        } elseif ($node->name instanceof Name) {
                            $funcName = strtolower($node->name->toString());
                            if (in_array($funcName, $this->bannedFunctions)) {
                                $this->violations[] = "Security Gate Violation: Panggilan fungsi sistem terlarang '{$funcName}()' terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                            }
                        }
                    }

                    return null;
                }
            };

            $traverser->addVisitor($visitor);
            $traverser->traverse($stmts);

            if (! empty($visitor->violations)) {
                throw new Exception($visitor->violations[0]);
            }

        } catch (Exception $e) {
            // Rethrow or wrap parsing exceptions to secure the upload
            throw new Exception("Analisis Keamanan Gagal pada {$filePath}: " . $e->getMessage());
        }
    }
}
