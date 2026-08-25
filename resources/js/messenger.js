window.Echo.join('presence-online')
    .here((users) => {
        window.messengerOnlineUsers = {};
        users.forEach((u) => {
            window.messengerOnlineUsers[u.id] = u;
        });
    })
    .joining((user) => {
        window.messengerOnlineUsers = window.messengerOnlineUsers || {};
        window.messengerOnlineUsers[user.id] = user;
    })
    .leaving((user) => {
        delete window.messengerOnlineUsers[user.id];
    });

window.messengerInit = function (config) {
    const { currentUserId, receiverId, csrfToken } = config;

    if (!receiverId) {
        return;
    }

    const channel = window.Echo.private('private-messenger.' + currentUserId);

    let typingTimer = null;

    channel.listen('MessageSent', (e) => {
        if (e.message.sender_id != receiverId) return;

        const chatArea = document.getElementById('chatArea');
        if (!chatArea) return;

        const div = document.createElement('div');
        div.className = 'message receiver';
        let html = '';
        if (e.message.attachment_url) {
            html += '<img src="' + e.message.attachment_url + '" class="attachment-preview" style="max-width:200px;border-radius:8px;margin-bottom:5px;">';
        }
        html += e.message.message || '';
        html += '<span class="time">' + e.message.created_at + '</span>';
        div.innerHTML = html;
        chatArea.appendChild(div);
        chatArea.scrollTop = chatArea.scrollHeight;
    });

    channel.listen('UserTyping', (e) => {
        if (e.user_id != receiverId) return;

        const typingEl = document.getElementById('typingIndicator');
        if (!typingEl) return;

        if (e.is_typing) {
            typingEl.textContent = e.user_name + ' is typing...';
        } else {
            typingEl.textContent = '';
        }
    });

    channel.listen('MessageRead', (e) => {
        const ticks = document.querySelectorAll('.tick');
        if (ticks.length) {
            ticks.forEach((t) => {
                t.textContent = '✓✓';
                t.classList.add('text-blue-500');
            });
        }
    });

    const messageInput = document.querySelector('input[name="message"]');
    if (messageInput) {
        messageInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            fetch('/typing', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    receiver_id: receiverId,
                    is_typing: true,
                }),
            });

            typingTimer = setTimeout(() => {
                fetch('/typing', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        receiver_id: receiverId,
                        is_typing: false,
                    }),
                });
            }, 1500);
        });
    }

    const chatAreaEl = document.getElementById('chatArea');
    if (chatAreaEl) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        fetch('/mark-read/' + receiverId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });
                    }
                });
            },
            { threshold: 0.5 }
        );
        observer.observe(chatAreaEl);
    }
};
