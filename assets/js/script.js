document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('cs-assistant-widget');
    const toggleBtn = document.getElementById('cs-assistant-toggle');
    const modal = document.getElementById('cs-assistant-modal');
    const closeBtn = document.getElementById('cs-assistant-close');
    const messagesContainer = document.getElementById('cs-assistant-messages');
    const inputField = document.getElementById('cs-assistant-input');
    const sendBtn = document.getElementById('cs-assistant-send');

    let chatHistory = [];
    let isProcessing = false;
    let userData = null;

    // Handle Lead Gen
    const leadForm = document.getElementById('cs-assistant-lead-form');
    if (leadForm) {
        inputField.disabled = true;
        sendBtn.disabled = true;

        document.getElementById('cs-lead-submit').addEventListener('click', () => {
            const name = document.getElementById('cs-lead-name').value.trim();
            const phone = document.getElementById('cs-lead-phone').value.trim();

            if (name && phone) {
                userData = { name, phone };
                leadForm.style.display = 'none';
                inputField.disabled = false;
                sendBtn.disabled = false;
                appendMessage('bot', csAssistantData.welcome_msg);
                inputField.focus();
            } else {
                alert('Silakan isi data diri Anda.');
            }
        });
    }

    // Add Handoff Button
    const showHandoff = () => {
        if (!csAssistantData.whatsapp_number) return;

        const existing = document.querySelector('.cs-assistant-handoff-container');
        if (existing) existing.remove();

        const container = document.createElement('div');
        container.className = 'cs-assistant-handoff-container';

        const waUrl = `https://wa.me/${csAssistantData.whatsapp_number}?text=Halo, saya ingin bertanya lebih lanjut setelah mengobrol dengan asisten.`;

        container.innerHTML = `
            <a href="${waUrl}" target="_blank" class="cs-assistant-handoff-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                ${csAssistantData.handoff_wording}
            </a>
        `;
        messagesContainer.appendChild(container);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    };

    // Toggle Modal
    toggleBtn.addEventListener('click', () => {
        modal.classList.toggle('hidden');
        if (!modal.classList.contains('hidden')) {
            if (!csAssistantData.enable_lead_gen && messagesContainer.children.length === 0) {
                appendMessage('bot', csAssistantData.welcome_msg);
            }
            if (!inputField.disabled) inputField.focus();
        }
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Send Message
    const sendMessage = async () => {
        const text = inputField.value.trim();
        if (!text || isProcessing) return;

        appendMessage('user', text);
        inputField.value = '';

        isProcessing = true;
        sendBtn.disabled = true;

        const typingIndicator = appendTypingIndicator();

        try {
            const formData = new FormData();
            formData.append('action', 'cs_assistant_get_chat_response');
            formData.append('message', text);
            if (userData) {
                formData.append('user_name', userData.name);
                formData.append('user_phone', userData.phone);
            }
            formData.append('history', JSON.stringify(chatHistory));
            formData.append('nonce', csAssistantData.nonce);

            const response = await fetch(csAssistantData.ajax_url, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            typingIndicator.remove();

            if (result.success) {
                const botMsg = result.data.message;
                appendMessage('bot', botMsg);
                chatHistory.push({ role: 'user', content: text });
                chatHistory.push({ role: 'assistant', content: botMsg });

                if (chatHistory.length >= 4) {
                    showHandoff();
                }
            } else {
                appendMessage('error', 'Error: ' + (result.data || 'Something went wrong'));
            }
        } catch (error) {
            typingIndicator.remove();
            appendMessage('error', 'Network error occurred.');
        } finally {
            isProcessing = false;
            sendBtn.disabled = false;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    };

    sendBtn.addEventListener('click', sendMessage);
    inputField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    function appendMessage(role, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `cs-assistant-msg ${role}`;

        if (role === 'bot') {
            let html = text;

            // 1. Handle Markdown Links: [text](url) -> <a href="url">text</a>
            html = html.replace(/\[(.*?)\]\((https?:\/\/.*?)\)/g, function (match, label, url) {
                return '<a href="' + url + '" target="_blank" class="cs-assistant-link">' + label + '</a>';
            });

            // 2. Handle Raw Links: http://... -> <a href="...">http://...</a>
            // (Only if not already inside an <a> tag)
            const urlRegex = /(?<!href="|">)(https?:\/\/[^\s<)]+)/g;
            html = html.replace(urlRegex, function (url) {
                let cleanUrl = url.replace(/[\.\,\?]+$/, '');
                return '<a href="' + cleanUrl + '" target="_blank" class="cs-assistant-link">' + cleanUrl + '</a>';
            });

            // 3. Handle Bold **text**
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // 4. Handle Headers
            html = html.replace(/^#{1,6}\s+(.*)/gm, '<strong>$1</strong>');

            // 5. Handle Bullet lists
            html = html.replace(/^\s*[\-\*]\s+(.*)/gm, '<li>$1</li>');

            // 6. Handle Numbered lists
            html = html.replace(/^\s*\d+\.\s+(.*)/gm, '<div>$&</div>');

            // 7. Final newline to BR
            html = html.replace(/\n/g, '<br>');

            msgDiv.innerHTML = html;
        } else {
            msgDiv.innerHTML = text.replace(/\n/g, '<br>');
        }

        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return msgDiv;
    }

    function appendTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'cs-assistant-typing';
        typingDiv.innerHTML = `
            <div class="cs-assistant-dot"></div>
            <div class="cs-assistant-dot"></div>
            <div class="cs-assistant-dot"></div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return typingDiv;
    }
});
