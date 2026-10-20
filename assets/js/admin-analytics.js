/* Owner dashboard enhancement. Reports and forms remain usable without JavaScript. */
(()=>{'use strict';
 // Exact values are already present in SVG titles and the native report tables.
 // Keep a persistent visible readout for mouse, keyboard and touch exploration.
 const showChartValue=event=>{
  const point=event.target.closest?.('[data-chart-tip]');if(!point)return;
  const readout=point.closest('.panel')?.querySelector('[data-chart-readout]');
  if(readout){readout.textContent=point.dataset.chartTip;readout.classList.add('has-value');}
 };
 document.addEventListener('pointerover',showChartValue);
 document.addEventListener('focusin',showChartValue);
 document.addEventListener('click',showChartValue);
 const reportLinks=document.querySelectorAll('.analytics-section-nav a');
 const selectReport=hash=>{
  for(const link of reportLinks){const selected=link.hash===hash;link.classList.toggle('is-current',selected);if(selected)link.setAttribute('aria-current','location');else link.removeAttribute('aria-current');}
 };
 for(const link of reportLinks)link.addEventListener('click',()=>selectReport(link.hash));
 window.addEventListener('hashchange',()=>selectReport(location.hash||'#traffic'));
 selectReport(location.hash||'#traffic');
 const panel=document.querySelector('[data-live-url]');if(!panel)return;
 const button=panel.querySelector('[data-live-toggle]'),status=panel.querySelector('[data-live-status]'),rows=panel.querySelector('[data-live-rows]'),count=document.querySelector('[data-live-count]');
 let paused=false,busy=false,stopped=false;
 button.hidden=false;
 const duration=n=>{n=Math.max(0,Math.round(Number(n)||0));return n<60?n+'s':n<3600?Math.floor(n/60)+'m '+n%60+'s':Math.floor(n/3600)+'h '+Math.floor(n%3600/60)+'m';};
 const cell=(tr,text)=>{const td=document.createElement('td');td.textContent=text;tr.append(td);return td;};
 const refresh=async()=>{
  if(paused||busy||stopped||document.visibilityState!=='visible')return;busy=true;
  try{
   const response=await fetch(panel.dataset.liveUrl,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});
   if(!response.ok||response.redirected)throw new Error('unavailable');
   const data=await response.json();if(!data.available||!Array.isArray(data.visitors))throw new Error('unavailable');
   const body=document.createDocumentFragment();
   for(const visitor of data.visitors.slice(0,100)){
    const tr=document.createElement('tr'),identity=cell(tr,''),dot=document.createElement('span');dot.className='analytics-live-dot';dot.setAttribute('aria-hidden','true');identity.append(dot);
    const recordId=Number(visitor.record_id),label='Visit '+String(visitor.id||'Unknown');
    if(Number.isSafeInteger(recordId)&&recordId>0&&panel.dataset.visitorsUrl){
     const link=document.createElement('a'),url=new URL(panel.dataset.visitorsUrl,location.href);url.searchParams.set('visit',String(recordId));link.href=url.href;link.textContent=label;identity.append(link);
    }else identity.append(document.createTextNode(label));
    const path=cell(tr,visitor.path||'/'),pages=document.createElement('small');pages.textContent=new Intl.NumberFormat().format(Math.max(0,Number(visitor.page_count)||0))+' pages in session';path.append(pages);
    cell(tr,visitor.referrer||'Direct / unknown');
    cell(tr,[visitor.city,visitor.region,visitor.country].filter(value=>value&&value!=='Unknown').join(', ')||'Unknown');
    const device=cell(tr,visitor.device||'Unknown'),software=document.createElement('small');software.textContent=[visitor.browser||'Unknown',visitor.os||'Unknown'].join(' · ');device.append(software);
    cell(tr,duration(visitor.active_seconds));cell(tr,String(visitor.last_seen||'').slice(11,19)+' UTC');body.append(tr);
   }
   if(!data.visitors.length){const tr=document.createElement('tr'),td=cell(tr,'No visitors active in the last minute.');td.colSpan=7;td.className='analytics-empty';body.append(tr);}
   rows.replaceChildren(body);count.textContent=new Intl.NumberFormat().format(Number(data.active_visitors)||0);
   status.textContent='Live · updated '+String(data.as_of||'')+' · refreshes every 15 seconds';
  }catch(error){status.textContent='Live update unavailable. The previous snapshot remains shown; retrying shortly.';}
  finally{busy=false;}
 };
 button.addEventListener('click',()=>{paused=!paused;button.textContent=paused?'Resume live updates':'Pause live updates';button.setAttribute('aria-pressed',String(paused));if(paused)status.textContent='Live updates paused. The last snapshot remains shown.';else refresh();});
 document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')refresh();});
 window.addEventListener('pagehide',()=>{stopped=true;});window.addEventListener('pageshow',()=>{stopped=false;refresh();});
 setInterval(refresh,15000);refresh();
})();
