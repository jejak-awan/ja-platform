<?php

declare(strict_types=1);

namespace Modules\Core\Helpers;

/**
 * CIDR Calculator Utility
 * Provides robust methods for IP address and CIDR block calculations.
 * Compliant with RFC 4632 (Classless Inter-Domain Routing).
 */
class CidrCalculator
{
    /**
     * Get the start and end IP long values for a given CIDR.
     *
     * @return array{start: int, end: int, count: int}|null
     */
    public static function getRange(string $cidr): ?array
    {
        if (! str_contains($cidr, '/')) {
            return null;
        }

        [$address, $mask] = explode('/', $cidr);
        $mask = (int) $mask;
        $start = ip2long($address);

        if ($start === false) {
            return null;
        }

        // Apply mask to find network address if not already
        $netmask = ~((1 << (32 - $mask)) - 1);
        $start = $start & $netmask;

        $count = (int) pow(2, 32 - $mask);
        $end = $start + $count - 1;

        return [
            'start' => $start,
            'end' => $end,
            'count' => $count,
        ];
    }

    /**
     * Check if an IP address is within a CIDR range.
     */
    public static function contains(string $cidr, string $ip): bool
    {
        $range = self::getRange($cidr);
        if (! $range) {
            return false;
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        return $ipLong >= $range['start'] && $ipLong <= $range['end'];
    }

    /**
     * Check if two CIDRs overlap.
     */
    public static function overlaps(string $cidr1, string $cidr2): bool
    {
        $range1 = self::getRange($cidr1);
        $range2 = self::getRange($cidr2);

        if (! $range1 || ! $range2) {
            return false;
        }

        return ($range1['start'] <= $range2['end']) && ($range2['start'] <= $range1['end']);
    }

    /**
     * Get the next subnet of a specific size after a given IP/CIDR.
     *
     * @param  int[]  $excludeLongs
     */
    public static function getNextSubnet(string $rootCidr, int $subnetSize, array $excludeLongs = []): ?string
    {
        $root = self::getRange($rootCidr);
        if (! $root) {
            return null;
        }

        $increment = (int) pow(2, 32 - $subnetSize);
        sort($excludeLongs);

        for ($current = $root['start']; $current <= $root['end']; $current += $increment) {
            $subnetEnd = $current + $increment - 1;

            if ($subnetEnd > $root['end']) {
                break;
            }

            $collision = false;
            foreach ($excludeLongs as $ip) {
                if ($ip >= $current && $ip <= $subnetEnd) {
                    $collision = true;
                    break;
                }
            }

            if (! $collision) {
                return long2ip($current).'/'.$subnetSize;
            }
        }

        return null;
    }
}
