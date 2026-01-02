document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('maia-chat-widget');
    const toggleBtn = document.getElementById('maia-chat-toggle');
    const modal = document.getElementById('maia-chat-modal');
    const closeBtn = document.getElementById('maia-chat-close');
    const messagesContainer = document.getElementById('maia-chat-messages');
    const inputField = document.getElementById('maia-chat-input');
    const sendBtn = document.getElementById('maia-chat-send');

    let chatHistory = [];
    let isProcessing = false;
    let userData = null;

    // Handle Lead Gen
    const leadForm = document.getElementById('maia-chat-lead-form');
    if (leadForm) {
        inputField.disabled = true;
        sendBtn.disabled = true;

        const leadSubmitBtn = document.getElementById('maia-lead-submit');
        if (leadSubmitBtn) {
            leadSubmitBtn.addEventListener('click', () => {
                const name = document.getElementById('maia-lead-name').value.trim();
                const phone = document.getElementById('maia-lead-phone').value.trim();

                if (name && phone) {
                    userData = { name, phone };
                    leadForm.style.display = 'none';
                    inputField.disabled = false;
                    sendBtn.disabled = false;
                    appendMessage('bot', maiaChatData.welcome_msg);
                    inputField.focus();
                } else {
                    alert('Silakan isi data diri Anda.');
                }
            });
        }
    }

    // Add Handoff Button
    const showHandoff = () => {
        if (!maiaChatData.whatsapp_number) return;

        const existing = document.querySelector('.maia-chat-handoff-container');
        if (existing) existing.remove();

        const container = document.createElement('div');
        container.className = 'maia-chat-handoff-container';

        const waUrl = `https://wa.me/${maiaChatData.whatsapp_number}?text=Halo, saya ingin bertanya lebih lanjut setelah mengobrol dengan asisten.`;

        container.innerHTML = `
            <a href="${waUrl}" target="_blank" class="maia-chat-handoff-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                ${maiaChatData.handoff_wording}
            </a>
        `;
        messagesContainer.appendChild(container);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    };

    // Toggle Modal
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            modal.classList.toggle('hidden');
            if (!modal.classList.contains('hidden')) {
                if (!maiaChatData.enable_lead_gen && messagesContainer.children.length === 0) {
                    appendMessage('bot', maiaChatData.welcome_msg);
                }
                if (!inputField.disabled) inputField.focus();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

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
            formData.append('action', 'maia_chat_get_response');
            formData.append('message', text);
            if (userData) {
                formData.append('user_name', userData.name);
                formData.append('user_phone', userData.phone);
            }
            formData.append('history', JSON.stringify(chatHistory));
            formData.append('nonce', maiaChatData.nonce);

            const response = await fetch(maiaChatData.ajax_url, {
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
            if (typingIndicator) typingIndicator.remove();
            appendMessage('error', 'Network error occurred.');
        } finally {
            isProcessing = false;
            sendBtn.disabled = false;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    };

    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }

    if (inputField) {
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    function appendMessage(role, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `maia-chat-msg ${role}`;

        if (role === 'bot') {
            let html = text;

            // 1. Handle Markdown Links
            html = html.replace(/\[(.*?)\]\((https?:\/\/.*?)\)/g, function (match, label, url) {
                return '<a href="' + url + '" target="_blank" class="maia-chat-link">' + label + '</a>';
            });

            // 2. Handle Raw Links
            const urlRegex = /(?<!href="|">)(https?:\/\/[^\s<)]+)/g;
            html = html.replace(urlRegex, function (url) {
                let cleanUrl = url.replace(/[\.\,\?]+$/, '');
                return '<a href="' + cleanUrl + '" target="_blank" class="maia-chat-link">' + cleanUrl + '</a>';
            });

            // 3. Handle Bold
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
        typingDiv.className = 'maia-chat-typing';
        typingDiv.innerHTML = `
            <div class="maia-chat-dot"></div>
            <div class="maia-chat-dot"></div>
            <div class="maia-chat-dot"></div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return typingDiv;
    }
});
