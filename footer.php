<footer>
    <div class="footer-container">
        <span><?php echo $t['footer_text']; ?></span>
        <div class="social-icons">
            <!-- Replace these links with your actual profile URLs -->
            <a href="https://www.facebook.com/abiangbam/about?locale=ms_MY" target="_blank" class="social-btn"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/bambamburgers_hq/" target="_blank" class="social-btn"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@bambamburger" target="_blank" class="social-btn"><i class="fab fa-tiktok"></i></a>
            <a href="https://wa.me/60175900799" target="_blank" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>

    <!-- Chatbot UI -->
    <div id="chatbot-container" style="position: fixed; bottom: 80px; right: 20px; z-index: 1001; font-family: 'Poppins', sans-serif;">
        <button id="chatbot-launcher" onclick="toggleChatWindow()" style="background: #FF6B00; color: white; border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="fas fa-comment-dots"></i>
        </button>

        <div id="chatbot-window" style="display: none; width: 320px; height: 450px; background: #1a1a1a; border: 1px solid #FF6B00; border-radius: 15px; flex-direction: column; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5); margin-bottom: 10px;">
            <div style="background: #FF6B00; color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-robot"></i> BamBam Assistant</span>
                <i class="fas fa-times" onclick="toggleChatWindow()" style="cursor: pointer;"></i>
            </div>
            <div id="chat-messages" style="flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; font-size: 14px; background: #222;">
                <div style="background: #333; color: white; padding: 8px 12px; border-radius: 10px; align-self: flex-start; max-width: 80%;">
                    Hello! How can I help you today? 🍔
                </div>
            </div>
            <div style="padding: 10px; border-top: 1px solid #333; display: flex; gap: 5px; background: #1a1a1a;">
                <input type="text" id="chat-input" placeholder="Type a message..." style="flex: 1; background: #333; border: 1px solid #444; color: white; border-radius: 20px; padding: 8px 15px; outline: none;">
                <button onclick="sendChatMessage()" style="background: #FF6B00; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
        footer {
            background: #FF6B00 !important; /* Consistent Orange */
            color: white;
            padding: 20px;
            text-align: center;
        }
        .footer-container { display: flex; justify-content: center; align-items: center; gap: 30px; flex-wrap: wrap; }
        .social-icons { display: flex; gap: 15px; }
        .social-btn { 
            color: white; 
            font-size: 1rem; 
            padding: 8px;
            width: 40px; height: 40px;
            background: transparent;
            border: 1px solid #ffffff; /* White Border */
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        .social-btn:hover { background: white; border-color: white; color: #FF6B00; transform: translateY(-3px); }
    </style>
</footer>

<script>
// Global Scroll Animation Observer
document.addEventListener("DOMContentLoaded", function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("active");
                observer.unobserve(entry.target); // Run animation only once
            }
        });
    }, { threshold: 0.1 });

    const reveals = document.querySelectorAll(".reveal");
    reveals.forEach((el) => observer.observe(el));
});

// Hide Loader Logic (with Fallback)
function hideLoader() {
    const loader = document.getElementById('loader-wrapper');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.visibility = 'hidden';
    }
}

window.addEventListener('load', hideLoader);
// Force hide after 3 seconds (Fallback for slow connections/errors)
setTimeout(hideLoader, 3000);

// Chatbot Logic
function toggleChatWindow() {
    const windowEl = document.getElementById('chatbot-window');
    if (windowEl.style.display === 'none' || windowEl.style.display === '') {
        windowEl.style.display = 'flex';
    } else {
        windowEl.style.display = 'none';
    }
}

async function sendChatMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;

    const chatMessages = document.getElementById('chat-messages');

    // Append User Message
    const userDiv = document.createElement('div');
    userDiv.style = "background: #FF6B00; color: white; padding: 8px 12px; border-radius: 10px; align-self: flex-end; max-width: 80%;";
    userDiv.textContent = message;
    chatMessages.appendChild(userDiv);
    input.value = '';
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // Show Loading
    const loadingDiv = document.createElement('div');
    loadingDiv.style = "background: #333; color: #aaa; padding: 8px 12px; border-radius: 10px; align-self: flex-start; max-width: 80%; font-style: italic;";
    loadingDiv.textContent = "Thinking...";
    chatMessages.appendChild(loadingDiv);

    try {
        const formData = new FormData();
        formData.append('message', message);

        const response = await fetch('chat_handler.php', { method: 'POST', body: formData });
        const text = await response.text();
        
        chatMessages.removeChild(loadingDiv);
        const botDiv = document.createElement('div');
        botDiv.style = "background: #333; color: white; padding: 8px 12px; border-radius: 10px; align-self: flex-start; max-width: 80%;";
        botDiv.textContent = text;
        chatMessages.appendChild(botDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } catch (e) {
        loadingDiv.textContent = "Error connecting to assistant.";
    }
}

document.getElementById('chat-input')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendChatMessage();
});
</script>

</body>
</html>