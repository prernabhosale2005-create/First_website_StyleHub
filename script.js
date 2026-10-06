function openModal(id){const el=document.getElementById(id);if(el)el.style.display='flex';}
function closeModal(id){const el=document.getElementById(id);if(el)el.style.display='none';}
function switchModal(closeId,openId){closeModal(closeId);openModal(openId);}
window.addEventListener('click',e=>{if(e.target.classList.contains('modal'))e.target.style.display='none';});

async function submitAuth(formId, messageId, action){
    const form=document.getElementById(formId); if(!form)return;
    form.addEventListener('submit',async e=>{
        e.preventDefault(); const data=new FormData(form); data.append('action',action);
        const message=document.getElementById(messageId);
        try{const response=await fetch('auth.php',{method:'POST',body:data});const result=await response.json();
            message.textContent=result.message;message.className='form-message '+(result.success?'success':'error');
            if(result.success)setTimeout(()=>location.reload(),500);
        }catch(err){message.textContent='Something went wrong. Please try again.';message.className='form-message error';}
    });
}
submitAuth('loginForm','loginMessage','login');submitAuth('registerForm','registerMessage','register');

document.querySelectorAll('.tab-btn').forEach(button=>button.addEventListener('click',function(){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
    this.classList.add('active');const target=document.getElementById(this.dataset.tab);if(target)target.classList.add('active');
}));

const searchInput=document.getElementById('searchInput');
if(searchInput){searchInput.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();e.target.form.submit();}});}

async function addToCart(productId,button){
    button.disabled=true; const data=new FormData();data.append('product_id',productId);data.append('quantity','1');
    try{const response=await fetch('add_to_cart.php',{method:'POST',body:data});const result=await response.json();
        if(result.success){button.textContent='Added ✓';const count=document.getElementById('cartCount');if(count)count.textContent=result.cart_count;setTimeout(()=>{button.textContent='Add to Cart';button.disabled=false;},900);}
        else{alert(result.message);button.disabled=false;}
    }catch(e){alert('Could not add this item. Please try again.');button.disabled=false;}
}
document.querySelectorAll('.add-cart').forEach(btn=>btn.addEventListener('click',()=>addToCart(btn.dataset.product,btn)));

if(new URLSearchParams(location.search).get('login')==='required')openModal('loginModal');
