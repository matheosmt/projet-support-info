document.addEventListener('DOMContentLoaded',()=>{
  const toggle=document.querySelector('[data-menu-toggle]');
  const menu=document.querySelector('[data-menu]');
  toggle?.addEventListener('click',()=>menu?.classList.toggle('open'));
  const canvas=document.getElementById('certChart');
  if(canvas){
    const labels=JSON.parse(canvas.dataset.labels||'[]'), values=JSON.parse(canvas.dataset.values||'[]');
    const ctx=canvas.getContext('2d'), dpr=window.devicePixelRatio||1, rect=canvas.getBoundingClientRect();
    canvas.width=rect.width*dpr; canvas.height=rect.height*dpr; ctx.scale(dpr,dpr);
    const w=rect.width,h=rect.height,p={l:40,r:18,t:20,b:35}; const max=Math.max(...values,10); const n=Math.max(values.length-1,1);
    ctx.font='12px DM Sans, sans-serif'; ctx.fillStyle='#8290a3'; ctx.strokeStyle='#e7ebf2';
    for(let i=0;i<4;i++){const y=p.t+i*(h-p.t-p.b)/3;ctx.beginPath();ctx.moveTo(p.l,y);ctx.lineTo(w-p.r,y);ctx.stroke();}
    ctx.beginPath(); values.forEach((v,i)=>{const x=p.l+i*(w-p.l-p.r)/n,y=h-p.b-(v/max)*(h-p.t-p.b);i?ctx.lineTo(x,y):ctx.moveTo(x,y)});ctx.strokeStyle='#3767ff';ctx.lineWidth=3;ctx.stroke();
    values.forEach((v,i)=>{const x=p.l+i*(w-p.l-p.r)/n,y=h-p.b-(v/max)*(h-p.t-p.b);ctx.beginPath();ctx.arc(x,y,4,0,Math.PI*2);ctx.fillStyle='#3767ff';ctx.fill();ctx.fillStyle='#8290a3';ctx.fillText(labels[i],x-12,h-10);});
  }
});
