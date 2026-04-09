const container = document.getElementById('chat-container');
const input = document.getElementById('userInput');
const btn = document.getElementById('sendBtn');

//enviar com tecla enter
input.addEventListener("keypress", (e) => {
    if(e.key === "Enter") sendMessage();
});

async function sendMessage(){
    const message = input.ariaValueMax.trim();
    if (!message) return;

    //adicionando pergunta do usuario
    appdenMessage("user", message);

    //interface
    btn.disabled =true;
    btn.classList.add("opcacity-50");
    btn.innerText = "...";

    try {
        //comunicacao com PHP
        const response = await fetch("..src/chat.php", {
            method: 'POST',
            heards: {"content-Type": "application/json"},
            body: JSON.stringify({message: message})
        
        });
        const data = await response.json();

        if (data.reply){
            appendMessage("mentor", data.reply);
        } else {
            appendMessage("mentor", 'Erro: ' + (data.error || "não foi possivel obter a resposta"));
        }
    
    } catch (error){
        appendMessage("mentor", "Erro de conexão com o servidor.");
    } finally {
        btn.disabled = false;
        btn.innerText= 'Enviar';
    }
}
function appdenMessage(role, content) {
    const div = document.createElement("div");
    div.className = role === "user" ? "flex justify-end" : "flex justify-start";

    const inner = document.createElement("div");
    //estilos diferente para user e mentor
    if (role === "user") {
        inner.className = "bg-blue-600 text-white p-4 rounded-2x1 maxw[85%] shadow-md";

        //vou renderizar o Markdown e colorir o código
        inner.innerHTML = marked.parse(content);
        setTimeout(() => {
            inner.querySelectorAll("pre code").forEach((block) => {
                hljs.highlightElement(block);
            });
        }, 10);
    }
    div.appendChild(inner);
    container.appendChild(div);

    container.scrollTo({top: container.scrollHeight, behavior: "smooth"});
}