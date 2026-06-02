import './bootstrap';
import '../css/app.css';
setTimeout(()=>document.querySelectorAll('.auto-dismiss').forEach((el)=>el.remove()),4000);
async function refreshNotificationCount(){const badge=document.querySelector('[data-notification-count]');if(!badge)return;try{const response=await fetch('/notifications/count',{headers:{'X-Requested-With':'XMLHttpRequest'}});const data=await response.json();badge.textContent=data.count;badge.classList.toggle('d-none',data.count<1);}catch(e){}}
setInterval(refreshNotificationCount,30000);refreshNotificationCount();
document.addEventListener('change',(event)=>{
    if(!event.target.matches('[data-check-all]'))return;
    const table=event.target.closest('table');
    table?.querySelectorAll('tbody input[type="checkbox"]:not(:disabled)').forEach((checkbox)=>{checkbox.checked=event.target.checked;});
});
document.addEventListener('submit',(event)=>{
    const form=event.target;
    if(!form.classList.contains('bulk-toolbar'))return;
    if(!document.querySelector(`input[form="${form.id}"]:checked`)){
        event.preventDefault();
        alert('Select at least one record first.');
    }
});
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('[data-image-preview]');
    if(!trigger)return;
    const src=trigger.dataset.imagePreview;
    if(!src)return;
    const modalEl=document.getElementById('imagePreviewModal');
    const image=modalEl?.querySelector('[data-image-preview-target]');
    if(!modalEl||!image||!window.bootstrap)return;
    image.src=src;
    new bootstrap.Modal(modalEl).show();
});
document.addEventListener('change',(event)=>{
    const select=event.target.closest('[data-template-target]');
    if(!select||!select.value)return;
    const target=document.querySelector(select.dataset.templateTarget);
    if(target)target.value=select.value;
});
document.querySelectorAll('[data-character-counter]').forEach((field)=>{
    const target=document.querySelector(field.dataset.characterCounter);
    const update=()=>{if(target)target.textContent=field.value.length;};
    field.addEventListener('input',update);
    update();
});
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('[data-smooth-scroll]');
    if(!trigger)return;
    const href=trigger.getAttribute('href')||'';
    if(!href.startsWith('#'))return;
    const target=document.querySelector(href);
    if(!target)return;
    event.preventDefault();
    target.scrollIntoView({behavior:'smooth',block:'start'});
});
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('[data-password-toggle]');
    if(!trigger)return;
    const input=document.querySelector(trigger.dataset.passwordToggle);
    if(!input)return;
    const visible=input.type==='text';
    input.type=visible?'password':'text';
    trigger.setAttribute('aria-label',visible?'Show password':'Hide password');
    const icon=trigger.querySelector('i');
    icon?.classList.toggle('fa-eye',visible);
    icon?.classList.toggle('fa-eye-slash',!visible);
    input.focus();
});
const passwordRules=[
    ['length',(value)=>value.length>=8],
    ['upper',(value)=>/[A-Z]/.test(value)],
    ['lower',(value)=>/[a-z]/.test(value)],
    ['number',(value)=>/[0-9]/.test(value)],
    ['symbol',(value)=>/[^A-Za-z0-9]/.test(value)],
];
function updatePasswordMeter(field){
    const form=field.closest('form')||document;
    const meter=form.querySelector('[data-password-meter]');
    if(!meter)return;
    const passed=passwordRules.filter(([,test])=>test(field.value)).length;
    meter.dataset.score=String(passed);
    const bar=meter.querySelector('.auth-password-meter-bar span');
    if(bar)bar.style.width=`${(passed/passwordRules.length)*100}%`;
    passwordRules.forEach(([name,test])=>{
        const rule=meter.querySelector(`[data-rule="${name}"]`);
        if(rule)rule.classList.toggle('is-met',test(field.value));
    });
}
document.querySelectorAll('[data-password-strength]').forEach((field)=>{
    field.addEventListener('input',()=>updatePasswordMeter(field));
    updatePasswordMeter(field);
});
document.addEventListener('keyup',(event)=>{
    const field=event.target.closest('[data-caps-lock]');
    if(!field||typeof event.getModifierState!=='function')return;
    const warning=field.closest('.mb-3')?.querySelector('[data-caps-lock-warning]');
    if(warning)warning.classList.toggle('is-visible',event.getModifierState('CapsLock'));
});
document.addEventListener('blur',(event)=>{
    const field=event.target.closest?.('[data-caps-lock]');
    const warning=field?.closest('.mb-3')?.querySelector('[data-caps-lock-warning]');
    if(warning)warning.classList.remove('is-visible');
},true);
document.addEventListener('click',(event)=>{
    const trigger=event.target.closest('.student-detail-button');
    if(!trigger)return;
    const modalEl=document.getElementById('studentItemPreviewModal');
    if(!modalEl||!window.bootstrap)return;
    const image=modalEl.querySelector('[data-student-preview-modal-image]');
    const imageWrap=modalEl.querySelector('.student-preview-image');
    modalEl.querySelector('[data-student-preview-modal-title]').textContent=trigger.dataset.studentPreviewTitle||'Found Item';
    modalEl.querySelector('[data-student-preview-modal-category]').textContent=trigger.dataset.studentPreviewCategory||'-';
    modalEl.querySelector('[data-student-preview-modal-date]').textContent=trigger.dataset.studentPreviewDate||'-';
    modalEl.querySelector('[data-student-preview-modal-location]').textContent=trigger.dataset.studentPreviewLocation||'-';
    modalEl.querySelector('[data-student-preview-modal-description]').textContent=trigger.dataset.studentPreviewDescription||'-';
    modalEl.querySelector('[data-student-preview-modal-claim]').href=trigger.dataset.studentPreviewClaim||'#';
    if(image&&imageWrap){
        image.src=trigger.dataset.studentPreviewImage||'';
        imageWrap.classList.toggle('is-empty',!trigger.dataset.studentPreviewImage);
    }
    new bootstrap.Modal(modalEl).show();
});
document.addEventListener('submit',(event)=>{
    const button=event.target.querySelector('button[type="submit"]:not([disabled]), button:not([type]):not([disabled])');
    if(button){
        const label=button.dataset.loadingText||'Working';
        button.dataset.originalText=button.innerHTML;
        button.innerHTML=`<i class="fa-solid fa-circle-notch fa-spin me-1"></i>${label}`;
        button.disabled=true;
    }
});
