function n(t,u=300){let e=null;return(...l)=>{e&&clearTimeout(e),e=setTimeout(()=>{t(...l),e=null},u)}}export{n as d};
