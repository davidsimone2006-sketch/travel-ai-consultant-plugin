/**
 * ALEX TRAVEL CONSULTANT - Main JavaScript
 * Handles UI interactions, chat logic, and lead capture
 */

(function() {
    'use strict';

    class AlexTravelConsultant {
        constructor() {
            this.sessionId = document.querySelector('[data-session-id]')?.dataset.sessionId;
            this.messages = [];
            this.travelData = {};
            this.isWaitingResponse = false;
            this.init();
        }

        init() {
            this.cacheElements();
            this.bindEvents();
            this.initThreeJS();
            console.log('🌍 Alex Travel Consultant Initialized');
        }

        cacheElements() {
            this.container = document.getElementById('alex-consultant-container');
            this.messagesContainer = document.getElementById('alex-messages');
            this.inputField = document.getElementById('alex-input');
            this.form = document.getElementById('alex-message-form');
            this.sendBtn = document.querySelector('.alex-send-btn');
            this.closeBtn = document.querySelector('.alex-close-btn');
            this.quickStartBtns = document.querySelectorAll('.quick-btn');
            this.detailsPanel = document.querySelector('.alex-details-panel');
            this.detailsContent = document.getElementById('alex-details-content');
            this.quickStartSection = document.getElementById('alex-quick-start');
        }

        bindEvents() {
            this.form.addEventListener('submit', (e) => this.handleSendMessage(e));
            this.closeBtn.addEventListener('click', () => this.closeConsultant());
            this.quickStartBtns.forEach(btn => {
                btn.addEventListener('click', (e) => this.handleQuickStart(e));
            });
        }

        handleSendMessage(e) {
            e.preventDefault();

            const message = this.inputField.value.trim();
            if (!message || this.isWaitingResponse) return;

            // Hide quick start section after first message
            if (this.quickStartSection) {
                this.quickStartSection.style.display = 'none';
            }

            // Add user message
            this.addMessage(message, 'user');
            this.inputField.value = '';
            this.isWaitingResponse = true;

            // Show typing indicator
            this.showTypingIndicator();

            // Get AI response
            this.fetchAIResponse(message);
        }

        handleQuickStart(e) {
            const action = e.target.dataset.action;
            const messages = {
                'flights': 'I\'m looking for flights',
                'hotels': 'I need to book hotels',
                'cruises': 'Tell me about cruise deals',
                'packages': 'Show me vacation packages',
            };

            this.inputField.value = messages[action];
            this.form.dispatchEvent(new Event('submit'));
        }

        addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `alex-message alex-message-${type}`;
            messageDiv.innerHTML = `<div class="message-content">${this.escapeHtml(text)}</div>`;
            this.messagesContainer.appendChild(messageDiv);
            this.scrollToBottom();

            this.messages.push({
                text: text,
                type: type,
                timestamp: new Date()
            });
        }

        showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'alex-message alex-message-ai';
            typingDiv.innerHTML = `<div class="message-content"><div class="alex-typing"><span></span><span></span><span></span></div></div>`;
            typingDiv.id = 'typing-indicator';
            this.messagesContainer.appendChild(typingDiv);
            this.scrollToBottom();
        }

        removeTypingIndicator() {
            const typing = document.getElementById('typing-indicator');
            if (typing) typing.remove();
        }

        fetchAIResponse(userMessage) {
            const data = {
                action: 'alex_get_consultation',
                message: userMessage,
                session_id: this.sessionId,
                nonce: alexConsultantData.nonce
            };

            fetch(alexConsultantData.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                this.removeTypingIndicator();
                this.isWaitingResponse = false;

                if (result.success) {
                    const response = result.data.response;
                    this.addMessage(response, 'ai');

                    // Extract and update travel data
                    if (result.data.extracted_data) {
                        this.travelData = { ...this.travelData, ...result.data.extracted_data };
                        this.updateDetailsPanel();
                    }

                    // Check if we have enough info to show lead form
                    this.checkLeadReadiness();
                } else {
                    this.addMessage('Sorry, something went wrong. Please try again.', 'ai');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.removeTypingIndicator();
                this.isWaitingResponse = false;
                this.addMessage('Sorry, I\'m having trouble connecting. Please try again.', 'ai');
            });
        }

        updateDetailsPanel() {
            if (Object.keys(this.travelData).length === 0) return;

            let html = '';
            if (this.travelData.destination) {
                html += `<div class="detail-item">
                    <div class="detail-label">📍 Destination</div>
                    <div class="detail-value">${this.escapeHtml(this.travelData.destination)}</div>
                </div>`;
            }
            if (this.travelData.budget) {
                html += `<div class="detail-item">
                    <div class="detail-label">💰 Budget</div>
                    <div class="detail-value">${this.escapeHtml(this.travelData.budget)}</div>
                </div>`;
            }
            if (this.travelData.travelers) {
                html += `<div class="detail-item">
                    <div class="detail-label">👥 Travelers</div>
                    <div class="detail-value">${this.escapeHtml(this.travelData.travelers)} people</div>
                </div>`;
            }
            if (this.travelData.service_type) {
                html += `<div class="detail-item">
                    <div class="detail-label">✈️ Service Type</div>
                    <div class="detail-value">${this.escapeHtml(this.travelData.service_type)}</div>
                </div>`;
            }

            this.detailsContent.innerHTML = html;
            this.detailsPanel.classList.add('active');
        }

        checkLeadReadiness() {
            // Show lead form button when user has provided enough info
            const requiredFields = ['destination', 'budget'];
            const hasRequired = requiredFields.some(field => this.travelData[field]);

            if (hasRequired && !document.getElementById('alex-lead-form-btn')) {
                const btn = document.createElement('button');
                btn.id = 'alex-lead-form-btn';
                btn.className = 'quick-btn';
                btn.style.cssText = 'background: linear-gradient(135deg, #d4af37 0%, #c19e1a 100%); color: #1a1a1a; grid-column: 1/-1; margin-top: 8px; font-weight: 600;';
                btn.textContent = '📋 Get Me Best Offers';
                btn.addEventListener('click', () => this.showLeadForm());

                const quickSection = document.querySelector('.quick-buttons');
                if (quickSection) {
                    quickSection.after(btn);
                }
            }
        }

        showLeadForm() {
            const formHtml = `
                <div class="alex-lead-form" style="padding: 20px; background: #fafafa; border-radius: 8px; margin: 16px 0;">
                    <h3 style="margin-top: 0; color: #1a1a1a;">📋 Get Personalized Offers</h3>
                    <p style="font-size: 13px; color: #666; line-height: 1.5;">Our travel experts will review your preferences and send you the best deals tailored to your budget.</p>
                    
                    <form id="alex-lead-submission" style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="text" placeholder="Your Name" id="lead-name" required style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;" />
                        <input type="email" placeholder="Your Email" id="lead-email" required style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;" />
                        <input type="tel" placeholder="Your Phone" id="lead-phone" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;" />
                        <textarea id="lead-preferences" placeholder="Any special requests or notes?" rows="2" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; resize: vertical;"></textarea>
                        <button type="submit" style="background: linear-gradient(135deg, #d4af37 0%, #c19e1a 100%); color: #1a1a1a; border: none; padding: 10px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;">
                            🚀 Send My Inquiry
                        </button>
                    </form>
                </div>
            `;

            this.messagesContainer.insertAdjacentHTML('beforeend', formHtml);
            this.scrollToBottom();

            document.getElementById('alex-lead-submission').addEventListener('submit', (e) => {
                this.submitLead(e);
            });
        }

        submitLead(e) {
            e.preventDefault();

            const name = document.getElementById('lead-name').value;
            const email = document.getElementById('lead-email').value;
            const phone = document.getElementById('lead-phone').value;
            const preferences = document.getElementById('lead-preferences').value;

            const leadData = {
                action: 'alex_submit_lead',
                name: name,
                email: email,
                phone: phone,
                destination: this.travelData.destination || '',
                travel_date: this.travelData.travel_date || '',
                budget: this.travelData.budget || '',
                service_type: this.travelData.service_type || '',
                travelers: this.travelData.travelers || '',
                preferences: preferences || '',
                conversation_summary: this.getConversationSummary(),
                nonce: alexConsultantData.nonce
            };

            fetch(alexConsultantData.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(leadData)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.showLeadConfirmation();
                } else {
                    alert('Error submitting your inquiry. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting your inquiry. Please try again.');
            });
        }

        getConversationSummary() {
            return this.messages
                .map(m => `${m.type === 'user' ? 'You' : 'Alex'}: ${m.text}`)
                .join('\n\n');
        }

        showLeadConfirmation() {
            const confirmHtml = `
                <div class="alex-message alex-message-welcome" style="margin-top: 16px;">
                    <div class="message-content" style="background: linear-gradient(135deg, #e8f5e9 0%, #f0f8ff 100%); border-left: 4px solid #4caf50;">
                        <h3 style="margin: 0 0 8px 0; color: #2e7d32;">✅ Perfect! We Got Your Inquiry!</h3>
                        <p style="margin: 6px 0; font-size: 13px; color: #333; line-height: 1.5;">
                            <strong>Thank you for choosing us!</strong><br>
                            Our expert travel advisors will review your preferences and send you personalized offers within <strong>24 hours</strong>.<br><br>
                            📧 Check your email for:
                        </p>
                        <ul style="margin: 8px 0; padding-left: 20px; font-size: 13px; color: #333; line-height: 1.6;">
                            <li>Confirmation of your travel inquiry</li>
                            <li>Best flight deals within your budget</li>
                            <li>Exclusive hotel offers</li>
                            <li>Cruise & package recommendations</li>
                            <li>Detailed itinerary options</li>
                        </ul>
                        <p style="margin: 8px 0; font-size: 13px; color: #333;">
                            <strong>Questions?</strong> Reply directly to the email and our team will help!
                        </p>
                    </div>
                </div>
            `;

            this.messagesContainer.insertAdjacentHTML('beforeend', confirmHtml);
            document.querySelector('.alex-lead-form').remove();
            this.scrollToBottom();
        }

        scrollToBottom() {
            setTimeout(() => {
                this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
            }, 0);
        }

        closeConsultant() {
            this.container.style.animation = 'slideOut 0.5s ease-out forwards';
            setTimeout(() => {
                this.container.remove();
            }, 500);
        }

        initThreeJS() {
            // Three.js initialization for subtle 3D background effect
            const canvas = document.getElementById('alex-3d-canvas');
            if (!canvas || typeof THREE === 'undefined') return;

            const width = canvas.clientWidth;
            const height = canvas.clientHeight;

            const scene = new THREE.Scene();
            scene.background = null;

            const camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
            camera.position.z = 5;

            const renderer = new THREE.WebGLRenderer({ 
                canvas: canvas, 
                alpha: true,
                antialias: true 
            });
            renderer.setSize(width, height);
            renderer.setPixelRatio(window.devicePixelRatio);

            // Create floating particles
            const geometry = new THREE.BufferGeometry();
            const particleCount = 30;
            const positions = new Float32Array(particleCount * 3);

            for (let i = 0; i < particleCount * 3; i += 3) {
                positions[i] = (Math.random() - 0.5) * 10;
                positions[i + 1] = (Math.random() - 0.5) * 10;
                positions[i + 2] = (Math.random() - 0.5) * 10;
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

            const material = new THREE.PointsMaterial({
                color: 0xd4af37,
                size: 0.1,
                opacity: 0.3,
                transparent: true
            });

            const particles = new THREE.Points(geometry, material);
            scene.add(particles);

            // Animation loop
            const animate = () => {
                requestAnimationFrame(animate);
                particles.rotation.x += 0.0002;
                particles.rotation.y += 0.0003;
                renderer.render(scene, camera);
            };

            animate();

            // Handle window resize
            window.addEventListener('resize', () => {
                const newWidth = canvas.clientWidth;
                const newHeight = canvas.clientHeight;
                camera.aspect = newWidth / newHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(newWidth, newHeight);
            });
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new AlexTravelConsultant();
        });
    } else {
        new AlexTravelConsultant();
    }

    // Add CSS animation for slide out
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateY(40px);
            }
        }
    `;
    document.head.appendChild(style);

})();
