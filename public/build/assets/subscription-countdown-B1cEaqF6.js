function g(){const e=document.getElementById("subscription-countdown");if(!e)return;const o=e.dataset.expiresAt;if(!o){e.innerHTML='<span class="text-muted small">No expiry date</span>';return}const l=new Date(o).getTime();function r(){var c;const p=Date.now(),t=l-p;if(t<=0){e.innerHTML=`
                <span class="badge bg-danger fw-bold">
                    <i class="bx bx-lock-alt me-1"></i>EXPIRED
                </span>
                <a href="/subscription/renew" class="ms-2 text-danger small fw-bold">Renew Now</a>`,(c=e.closest(".subscription-wrapper"))==null||c.classList.add("expired"),clearInterval(d);return}const n=Math.floor(t/(1e3*60*60*24)),b=Math.floor(t%(1e3*60*60*24)/(1e3*60*60)),f=Math.floor(t%(1e3*60*60)/(1e3*60)),u=Math.floor(t%(1e3*60)/1e3),s=x=>String(x).padStart(2,"0");let a="bg-success",i="bx-time-five";n<3?(a="bg-danger",i="bx-error-circle"):n<7&&(a="bg-warning text-dark",i="bx-alarm-exclamation"),e.innerHTML=`
            <span class="badge ${a} d-inline-flex align-items-center gap-1 px-2 py-1">
                <i class="bx ${i}"></i>
                ${n}d ${s(b)}h ${s(f)}m ${s(u)}s
            </span>`}r();const d=setInterval(r,1e3)}document.addEventListener("DOMContentLoaded",g);
