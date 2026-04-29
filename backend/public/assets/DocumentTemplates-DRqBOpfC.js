import{a as e}from"./rolldown-runtime-BYbx6iT9.js";import{C as t,G as n,J as r,O as i,Qt as a,S as o,St as s,T as c,X as l,g as u,j as d,jt as f,k as p,ot as m,rn as h,ut as g,w as _,x as ee}from"./vue.runtime.esm-bundler-DOqi2ZHr.js";import{i as te}from"./responseParser-Bgbun8l9.js";import{n as ne,r as re,t as v}from"./TabsTrigger-DK5gE9OG.js";import{t as y}from"./TabsContent-BDjX8u0e.js";import{t as ie}from"./Input-D3UATjN7.js";import{a as ae,i as b,n as x,r as S,t as C,u as w}from"./DialogTitle-D3ygq92m.js";import{t as T}from"./Button-a6QijzR7.js";import{a as oe,i as se,n as ce,r as E,t as D}from"./SelectValue-BfVL_kL3.js";import{$ as O,U as k,o as A,p as j,q as M,tt as N}from"./ui-D40252jZ.js";import{t as P}from"./useToast-C3E758gr.js";import{t as F}from"./OperationsService-CG3_1k3b.js";import{t as I}from"./dayjs.min-CNejvMo8.js";var L=e(I(),1),R={class:`space-y-6`},z={class:`flex flex-col md:flex-row justify-between items-start md:items-center gap-4`},B={class:`text-xl font-bold text-foreground`},V={class:`text-sm text-muted-foreground italic`},H={key:0,class:`flex justify-center py-12`},U={key:1,class:`text-center py-12 bg-muted/20 rounded-2xl border border-dashed border-border`},W={class:`font-bold text-lg`},G={class:`text-muted-foreground text-sm max-w-md mx-auto`},le={key:2,class:`grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`},ue={class:`flex justify-between items-start`},de={class:`flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity`},fe={class:`font-bold text-lg leading-tight`},pe={class:`text-xs text-muted-foreground`},me={class:`flex items-center gap-4 text-xs`},he={key:0,class:`flex items-center gap-1.5 text-primary`},K={class:`p-3 bg-muted/30 border-t border-border/40 flex justify-end`},ge={key:0,class:`flex flex-col flex-1 overflow-hidden`},_e={class:`grid grid-cols-2 gap-4 py-4 border-b border-border/50`},ve={class:`space-y-2`},ye={class:`text-xs font-bold uppercase`},be={class:`space-y-2`},xe={class:`text-xs font-bold uppercase`},Se={class:`grid grid-cols-1 md:grid-cols-2 gap-4`},Ce={class:`space-y-2`},we={class:`text-xs font-bold uppercase`},Te={class:`space-y-2`},Ee={class:`text-xs font-bold uppercase`},De={class:`flex items-center gap-6`},Oe={class:`flex items-center space-x-2`},ke={for:`is_active`,class:`text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70`},Ae={class:`flex items-center space-x-2`},je={for:`is_default`,class:`text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70`},Me={class:`p-3 bg-primary/5 rounded-xl border border-primary/10`},Ne={class:`text-[10px] font-bold uppercase tracking-wider text-primary mb-2`},Pe={class:`flex flex-wrap gap-2`},Fe={class:`w-full h-full border border-border rounded-2xl overflow-auto bg-white p-8`},Ie=[`innerHTML`],q=d({__name:`DocumentTemplates`,setup(e){let d=P(),I=s(!0),q=s([]),J=s(null),Y=async()=>{I.value=!0;try{q.value=te(await F.getDocumentTemplates()).data}catch(e){d.error.fromResponse(e)}finally{I.value=!1}},Le=e=>(0,L.default)(e).format(`DD MMM YYYY HH:mm`),Re=e=>e===`skl`?`bg-blue-100 text-blue-700`:e===`certificate`?`bg-amber-100 text-amber-700`:`bg-slate-100 text-slate-700`,X=s(!1),Z=s(null),Q=s(!1),ze=ee(()=>{if(!Z.value?.content)return``;let e=Z.value.content,t={full_name:`Ahmad Fauzi`,nisn:`0012345678`,nis:`12345`,gender:`Laki-laki`,birth_place:`Jakarta`,birth_date:`12 Mei 2006`,level_name:`XII RPL 1`,department_name:`Rekayasa Perangkat Lunak`,graduation_year:`2024`,certificate_number:`SKL/2024/001`,published_date:(0,L.default)().format(`DD MMMM YYYY`)};return Object.keys(t).forEach(n=>{let r=RegExp(`\\{\\{\\s*\\$${n}\\s*\\}\\}`,`g`);e=e.replace(r,t[n])}),e=e.replace(/\{\{\s*\$grades\[".*?"\]\s*\}\}/g,`90`),e}),Be=[`{{ $full_name }}`,`{{ $nisn }}`,`{{ $nis }}`,`{{ $gender }}`,`{{ $birth_place }}`,`{{ $birth_date }}`,`{{ $level_name }}`,`{{ $department_name }}`,`{{ $graduation_year }}`,`{{ $certificate_number }}`,`{{ $published_date }}`,`{{ $grades["Mapel Name"] }}`],$={skl:{content:`<div class="skl-container">
  <div class="header">
    <div class="school-logo">[LOGO]</div>
    <div class="school-info">
      <h1 class="school-name">NAMA SEKOLAH ANDA</h1>
      <p class="school-address">Jl. Alamat Sekolah No. 123, Kota, Provinsi</p>
      <p class="school-contact">Telp: (021) 123456 | Website: www.sekolahanda.sch.id</p>
    </div>
  </div>
  
  <hr class="header-line">
  
  <div class="document-title">
    <h2>SURAT KETERANGAN LULUS</h2>
    <p class="cert-number">Nomor: {{ $certificate_number }}</p>
  </div>
  
  <div class="body-content">
    <p>Yang bertanda tangan di bawah ini, Kepala Sekolah Menengah Kejuruan [NAMA SEKOLAH], menerangkan bahwa:</p>
    
    <table class="student-info">
      <tr><td>Nama Lengkap</td><td>:</td><td class="bold">{{ $full_name }}</td></tr>
      <tr><td>Tempat, Tanggal Lahir</td><td>:</td><td>{{ $birth_place }}, {{ $birth_date }}</td></tr>
      <tr><td>Nomor Induk Siswa (NIS)</td><td>:</td><td>{{ $nis }}</td></tr>
      <tr><td>Nomor Induk Siswa Nasional (NISN)</td><td>:</td><td>{{ $nisn }}</td></tr>
      <tr><td>Kompetensi Keahlian</td><td>:</td><td>{{ $department_name }}</td></tr>
    </table>
    
    <p class="status-text">Berdasarkan hasil rapat pleno dewan guru pada tanggal [TANGGAL RAPAT], siswa tersebut di atas dinyatakan:</p>
    
    <div class="status-box">LULUS</div>
    
    <p>dari satuan pendidikan [NAMA SEKOLAH] tahun pelajaran {{ $graduation_year }}. Surat keterangan ini dapat digunakan untuk keperluan administrasi sementara hingga ijazah asli diterbitkan.</p>
  </div>
  
  <div class="footer">
    <div class="signature">
      <p>Kota Sekolah, {{ $published_date }}</p>
      <p>Kepala Sekolah,</p>
      <div class="sig-space"></div>
      <p class="bold">[NAMA KEPALA SEKOLAH]</p>
      <p>NIP. [NOMOR INDUK PEGAWAI]</p>
    </div>
  </div>
</div>`,styles:`.skl-container { 
  width: 100%; 
  max-width: 800px; 
  margin: 0 auto; 
  padding: 40px; 
  font-family: 'Times New Roman', Times, serif;
  color: #000;
  background: #fff;
  line-height: 1.6;
}
.header { display: flex; align-items: center; margin-bottom: 10px; }
.school-logo { width: 80px; height: 80px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; margin-right: 20px; }
.school-info { flex: 1; text-align: center; }
.school-name { margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; }
.school-address, .school-contact { margin: 0; font-size: 12px; }
.header-line { border: 2px solid #000; margin-bottom: 20px; }
.document-title { text-align: center; margin-bottom: 30px; }
.document-title h2 { margin: 0; text-decoration: underline; font-size: 20px; }
.cert-number { margin: 5px 0 0; font-size: 14px; }
.student-info { width: 90%; margin: 20px auto; border-collapse: collapse; }
.student-info td { padding: 5px; vertical-align: top; font-size: 14px; }
.student-info td:first-child { width: 200px; }
.student-info td:nth-child(2) { width: 20px; }
.bold { font-weight: bold; }
.status-text { margin-top: 20px; text-align: justify; font-size: 14px; }
.status-box { width: 200px; margin: 20px auto; border: 3px double #000; padding: 10px; text-align: center; font-size: 24px; font-weight: bold; }
.footer { margin-top: 40px; display: flex; justify-content: flex-end; }
.signature { text-align: center; width: 250px; }
.sig-space { height: 80px; }`},certificate:{content:`<div class="cert-border">
  <div class="cert-inner">
    <div class="cert-header">
      <div class="cert-school-logo">[LOGO]</div>
      <h1>SERTIFIKAT PENGHARGAAN</h1>
      <p class="cert-subtitle">Diberikan Kepada:</p>
    </div>
    
    <div class="cert-body">
      <h2 class="cert-student-name">{{ $full_name }}</h2>
      <p class="cert-text">Atas prestasi dan dedikasi luar biasa dalam menyelesaikan pendidikan di [NAMA SEKOLAH] Program Keahlian {{ $department_name }} dengan predikat memuaskan pada tahun pelajaran {{ $graduation_year }}.</p>
    </div>
    
    <div class="cert-footer">
      <div class="cert-sign">
        <p>{{ $published_date }}</p>
        <p>Kepala Sekolah,</p>
        <div class="sig-placeholder"></div>
        <p class="bold">[NAMA KEPALA SEKOLAH]</p>
      </div>
    </div>
  </div>
</div>`,styles:`.cert-border {
  width: 800px;
  height: 560px;
  padding: 20px;
  background: #fdf6e3;
  border: 10px solid #c9a55c;
  margin: 0 auto;
  position: relative;
  font-family: 'Garamond', serif;
}
.cert-inner {
  border: 2px solid #c9a55c;
  height: 100%;
  padding: 40px;
  text-align: center;
}
.cert-header h1 {
  font-size: 36px;
  color: #8c6d31;
  margin: 20px 0;
  letter-spacing: 2px;
}
.cert-student-name {
  font-size: 32px;
  border-bottom: 2px solid #333;
  display: inline-block;
  padding: 0 40px;
  margin: 20px 0;
  color: #333;
}
.cert-text { font-size: 18px; line-height: 1.5; margin: 20px auto; max-width: 600px; }
.cert-footer { margin-top: 40px; display: flex; justify-content: flex-end; }
.cert-sign { width: 200px; }
.sig-placeholder { height: 60px; }`},letter:{content:`<div class="letter-container">
  <div class="letter-head">
    <h3>YAYASAN PENDIDIKAN [NAMA YAYASAN]</h3>
    <h2>[NAMA SEKOLAH ANDA]</h2>
    <p>Status: Terakreditasi A | NSS: 123456789 | NPSN: 98765432</p>
    <p>Alamat: Jl. Pendidikan No. 1, Kota | Email: info@sekolah.sch.id</p>
    <hr class="double-line">
  </div>
  
  <div class="letter-meta">
    <p>Nomor: {{ $certificate_number }}</p>
    <p>Lampiran: -</p>
    <p>Perihal: Surat Keterangan Siswa</p>
  </div>
  
  <div class="letter-title">
    <h3>SURAT KETERANGAN</h3>
  </div>
  
  <div class="letter-body">
    <p>Kepala [NAMA SEKOLAH ANDA] dengan ini menerangkan bahwa:</p>
    <div class="letter-student">
      <p>Nama: <b>{{ $full_name }}</b></p>
      <p>NIS/NISN: {{ $nis }} / {{ $nisn }}</p>
      <p>Kelas: {{ $level_name }}</p>
    </div>
    <p>Adalah benar-benar siswa yang terdaftar aktif pada tahun pelajaran {{ $graduation_year }}. Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
  </div>
  
  <div class="letter-footer">
    <div class="letter-sign">
      <p>Dikeluarkan di: [KOTA]</p>
      <p>Pada tanggal: {{ $published_date }}</p>
      <p>Kepala Sekolah,</p>
      <div class="sig-gap"></div>
      <p><b>[NAMA KEPALA SEKOLAH]</b></p>
    </div>
  </div>
</div>`,styles:`.letter-container { padding: 50px; background: white; min-height: 800px; color: black; font-family: Arial, sans-serif; }
.letter-head { text-align: center; margin-bottom: 20px; }
.letter-head h2, .letter-head h3 { margin: 0; }
.double-line { border-top: 3px double black; margin-top: 10px; }
.letter-meta { margin: 30px 0; }
.letter-title { text-align: center; text-decoration: underline; margin-bottom: 30px; }
.letter-student { margin: 20px 50px; }
.letter-footer { margin-top: 50px; display: flex; justify-content: flex-end; }
.letter-sign { text-align: left; }
.sig-gap { height: 70px; }`}},Ve=()=>{Z.value={name:``,type:`skl`,content:$.skl.content,styles:$.skl.styles,is_active:!0,is_default:!1},X.value=!0};m(()=>Z.value?.type,e=>{if(e&&!Z.value?.id){let t=Z.value.content;Object.values($).some(e=>e.content===t)&&$[e]&&(Z.value.content=$[e].content,Z.value.styles=$[e].styles)}});let He=e=>{Z.value={...e},X.value=!0},Ue=async()=>{Q.value=!0;try{Z.value.id?await F.updateDocumentTemplate(Z.value.id,Z.value):await F.storeDocumentTemplate(Z.value),d.success.action(`Template berhasil disimpan`),X.value=!1,Y()}catch(e){d.error.fromResponse(e)}finally{Q.value=!1}},We=async e=>{if(await J.value.confirm({title:`Hapus Template`,message:`Apakah Anda yakin ingin menghapus template "${e.name}"?`,variant:`destructive`}))try{await F.deleteDocumentTemplate(e.id),d.success.action(`Template berhasil dihapus`),Y()}catch(e){d.error.fromResponse(e)}},Ge=e=>{let t=window.open(``,`_blank`);t&&(t.document.write(`
            <html>
                <head>
                    <style>${e.styles}</style>
                </head>
                <body>
                    ${e.content.replace(/\{\{\s*\$full_name\s*\}\}/g,`CONTOH NAMA SISWA`)}
                </body>
            </html>
        `),t.document.close())};return n(Y),(e,n)=>(r(),c(`div`,R,[o(`div`,z,[o(`div`,null,[o(`h2`,B,h(e.$t(`features.school.ops.templates.title`)),1),o(`p`,V,h(e.$t(`features.school.ops.templates.subtitle`)),1)]),p(f(T),{variant:`default`,class:`rounded-xl shadow-sm px-6`,onClick:Ve},{default:g(()=>[p(f(j),{name:`Plus`,class:`w-4 h-4 mr-2`}),i(` `+h(e.$t(`features.school.ops.templates.btnAdd`)),1)]),_:1})]),I.value?(r(),c(`div`,H,[p(f(j),{name:`RefreshCcw`,class:`w-8 h-8 animate-spin text-primary/40`})])):q.value.length===0?(r(),c(`div`,U,[p(f(j),{name:`FileText`,class:`w-12 h-12 mx-auto mb-4 text-muted-foreground/40`}),o(`h3`,W,h(e.$t(`features.school.ops.templates.emptyTitle`)),1),o(`p`,G,h(e.$t(`features.school.ops.templates.emptySubtitle`)),1)])):(r(),c(`div`,le,[(r(!0),c(u,null,l(q.value,n=>(r(),t(f(M),{key:n.id,class:`border border-border/40 shadow-sm rounded-2xl overflow-hidden group hover:border-primary/40 transition-all`},{default:g(()=>[p(f(k),{class:`p-6 space-y-4`},{default:g(()=>[o(`div`,ue,[o(`div`,{class:a([`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest`,Re(n.type)])},h(n.type),3),o(`div`,de,[p(f(T),{variant:`ghost`,size:`icon`,class:`h-8 w-8 text-primary`,onClick:e=>He(n)},{default:g(()=>[p(f(j),{name:`Edit`,class:`w-3.5 h-3.5`})]),_:1},8,[`onClick`]),p(f(T),{variant:`ghost`,size:`icon`,class:`h-8 w-8 text-destructive`,onClick:e=>We(n)},{default:g(()=>[p(f(j),{name:`Trash2`,class:`w-3.5 h-3.5`})]),_:1},8,[`onClick`])])]),o(`div`,null,[o(`h3`,fe,h(n.name),1),o(`p`,pe,h(e.$t(`features.school.ops.templates.lastUpdated`))+`: `+h(Le(n.updated_at)),1)]),o(`div`,me,[o(`div`,{class:a([`flex items-center gap-1.5`,n.is_active?`text-success`:`text-muted-foreground`])},[o(`div`,{class:a([`w-1.5 h-1.5 rounded-full`,n.is_active?`bg-success`:`bg-muted-foreground`])},null,2),i(` `+h(n.is_active?e.$t(`features.school.ops.templates.labels.active`):e.$t(`features.school.ops.templates.labels.inactive`)),1)],2),n.is_default?(r(),c(`div`,he,[p(f(j),{name:`Star`,class:`w-3 h-3 fill-primary`}),i(` `+h(e.$t(`features.school.ops.templates.labels.default`)),1)])):_(``,!0)])]),_:2},1024),o(`div`,K,[p(f(T),{variant:`outline`,size:`sm`,class:`h-8 rounded-lg text-xs`,onClick:e=>Ge(n)},{default:g(()=>[i(h(e.$t(`features.school.ops.templates.previewHtml`)),1)]),_:1},8,[`onClick`])])]),_:2},1024))),128))])),p(f(w),{open:X.value,"onUpdate:open":n[7]||=e=>X.value=e},{default:g(()=>[p(f(ae),{class:`sm:max-w-[900px] max-h-[90vh] overflow-hidden flex flex-col rounded-2xl`},{default:g(()=>[p(f(x),null,{default:g(()=>[p(f(C),null,{default:g(()=>[i(h(Z.value?.id?`Edit Template`:`Tambah Template`),1)]),_:1}),p(f(b),null,{default:g(()=>[...n[8]||=[i(` Gunakan HTML & CSS untuk mendesain template. Gunakan placeholder seperti `,-1),o(`code`,null,`{{ $full_name }}`,-1),i(` untuk data dinamis. `,-1)]]),_:1})]),_:1}),Z.value?(r(),c(`div`,ge,[o(`div`,_e,[o(`div`,ve,[o(`label`,ye,h(e.$t(`features.school.ops.templates.modal.labelName`)),1),p(f(ie),{modelValue:Z.value.name,"onUpdate:modelValue":n[0]||=e=>Z.value.name=e,placeholder:e.$t(`features.school.ops.templates.modal.placeholderName`),class:`rounded-xl`},null,8,[`modelValue`,`placeholder`])]),o(`div`,be,[o(`label`,xe,h(e.$t(`features.school.ops.templates.modal.labelType`)),1),p(f(oe),{modelValue:Z.value.type,"onUpdate:modelValue":n[1]||=e=>Z.value.type=e},{default:g(()=>[p(f(ce),{class:`rounded-xl`},{default:g(()=>[p(f(D))]),_:1}),p(f(se),null,{default:g(()=>[p(f(E),{value:`skl`},{default:g(()=>[i(h(e.$t(`features.school.ops.templates.modal.types.skl`)),1)]),_:1}),p(f(E),{value:`certificate`},{default:g(()=>[i(h(e.$t(`features.school.ops.templates.modal.types.certificate`)),1)]),_:1}),p(f(E),{value:`letter`},{default:g(()=>[i(h(e.$t(`features.school.ops.templates.modal.types.letter`)),1)]),_:1})]),_:1})]),_:1},8,[`modelValue`])])]),p(f(re),{"default-value":`editor`,class:`flex-1 flex flex-col overflow-hidden mt-4`},{default:g(()=>[p(f(ne),{class:`grid w-full grid-cols-2 lg:w-[300px]`},{default:g(()=>[p(f(v),{value:`editor`},{default:g(()=>[...n[9]||=[i(`Editor`,-1)]]),_:1}),p(f(v),{value:`preview`},{default:g(()=>[i(h(e.$t(`features.school.ops.templates.previewHtml`)),1)]),_:1})]),_:1}),p(f(y),{value:`editor`,class:`flex-1 overflow-y-auto space-y-4 py-4 pr-2`},{default:g(()=>[o(`div`,Se,[o(`div`,Ce,[o(`label`,we,h(e.$t(`features.school.ops.templates.modal.labelHtml`)),1),p(f(O),{modelValue:Z.value.content,"onUpdate:modelValue":n[2]||=e=>Z.value.content=e,placeholder:`<div class='certificate'>...</div>`,class:`rounded-xl font-mono text-xs min-h-[300px]`},null,8,[`modelValue`])]),o(`div`,Te,[o(`label`,Ee,h(e.$t(`features.school.ops.templates.modal.labelCss`)),1),p(f(O),{modelValue:Z.value.styles,"onUpdate:modelValue":n[3]||=e=>Z.value.styles=e,placeholder:`.certificate { padding: 20px; }`,class:`rounded-xl font-mono text-xs min-h-[300px]`},null,8,[`modelValue`])])]),o(`div`,De,[o(`div`,Oe,[p(f(N),{id:`is_active`,checked:Z.value.is_active,"onUpdate:checked":n[4]||=e=>Z.value.is_active=e},null,8,[`checked`]),o(`label`,ke,h(e.$t(`features.school.ops.templates.modal.labelActive`)),1)]),o(`div`,Ae,[p(f(N),{id:`is_default`,checked:Z.value.is_default,"onUpdate:checked":n[5]||=e=>Z.value.is_default=e},null,8,[`checked`]),o(`label`,je,h(e.$t(`features.school.ops.templates.modal.labelDefault`)),1)])]),o(`div`,Me,[o(`p`,Ne,h(e.$t(`features.school.ops.templates.modal.placeholdersTitle`)),1),o(`div`,Pe,[(r(),c(u,null,l(Be,e=>o(`span`,{key:e,class:`px-2 py-0.5 bg-white border border-border rounded text-[10px] font-mono`},h(e),1)),64))])])]),_:1}),p(f(y),{value:`preview`,class:`flex-1 overflow-hidden py-4`},{default:g(()=>[o(`div`,Fe,[Z.value.styles?(r(),c(u,{key:0},[],64)):_(``,!0),o(`div`,{innerHTML:ze.value},null,8,Ie)])]),_:1})]),_:1})])):_(``,!0),p(f(S),null,{default:g(()=>[p(f(T),{variant:`outline`,class:`rounded-xl`,onClick:n[6]||=e=>X.value=!1},{default:g(()=>[...n[10]||=[i(` Batal `,-1)]]),_:1}),p(f(T),{variant:`default`,class:`rounded-xl px-8`,loading:Q.value,onClick:Ue},{default:g(()=>[...n[11]||=[i(` Simpan Template `,-1)]]),_:1},8,[`loading`])]),_:1})]),_:1})]),_:1},8,[`open`]),p(f(A),{ref_key:`confirmModal`,ref:J},null,512)]))}});export{q as default};