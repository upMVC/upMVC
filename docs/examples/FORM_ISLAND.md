# Form Island Example

## Overview

A **Form Island** provides real-time validation, rich interactions, and enhanced UX for forms within a server-rendered page. This example demonstrates:

- ✅ Real-time validation
- ✅ Field-level error messages
- ✅ Password strength meter
- ✅ File upload with preview
- ✅ Multi-step forms
- ✅ Progressive enhancement (works without JS)

---

## Simple Contact Form Island

```php
<?php
// modules/contact/View.php

class View {
    private function contactFormIsland() {
        ?>
        <script type="module">
            import { render } from 'preact';
            import { useState } from 'preact/hooks';
            import { html } from 'htm/preact';

            function ContactForm() {
                const [formData, setFormData] = useState({
                    name: '',
                    email: '',
                    message: ''
                });
                
                const [errors, setErrors] = useState({});
                const [touched, setTouched] = useState({});
                const [submitting, setSubmitting] = useState(false);
                const [success, setSuccess] = useState(false);

                // Real-time validation
                const validate = (field, value) => {
                    const newErrors = { ...errors };

                    switch (field) {
                        case 'name':
                            if (!value || value.length < 2) {
                                newErrors.name = 'Name must be at least 2 characters';
                            } else {
                                delete newErrors.name;
                            }
                            break;

                        case 'email':
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!value) {
                                newErrors.email = 'Email is required';
                            } else if (!emailRegex.test(value)) {
                                newErrors.email = 'Invalid email format';
                            } else {
                                delete newErrors.email;
                            }
                            break;

                        case 'message':
                            if (!value || value.length < 10) {
                                newErrors.message = 'Message must be at least 10 characters';
                            } else if (value.length > 500) {
                                newErrors.message = 'Message too long (max 500 characters)';
                            } else {
                                delete newErrors.message;
                            }
                            break;
                    }

                    setErrors(newErrors);
                    return Object.keys(newErrors).length === 0;
                };

                // Handle field change
                const handleChange = (field, value) => {
                    setFormData({ ...formData, [field]: value });
                    
                    // Validate only if field was touched
                    if (touched[field]) {
                        validate(field, value);
                    }
                };

                // Handle field blur
                const handleBlur = (field) => {
                    setTouched({ ...touched, [field]: true });
                    validate(field, formData[field]);
                };

                // Handle submit
                const handleSubmit = async (e) => {
                    e.preventDefault();
                    
                    // Mark all fields as touched
                    const allTouched = Object.keys(formData).reduce((acc, key) => {
                        acc[key] = true;
                        return acc;
                    }, {});
                    setTouched(allTouched);

                    // Validate all fields
                    let isValid = true;
                    Object.keys(formData).forEach(field => {
                        if (!validate(field, formData[field])) {
                            isValid = false;
                        }
                    });

                    if (!isValid) return;

                    // Submit form
                    setSubmitting(true);
                    
                    try {
                        const response = await fetch('<?php echo BASE_URL; ?>/contact/submit', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            setSuccess(true);
                            setFormData({ name: '', email: '', message: '' });
                            setTouched({});
                        }
                    } catch (error) {
                        console.error('Submit failed:', error);
                    } finally {
                        setSubmitting(false);
                    }
                };

                if (success) {
                    return html`
                        <div class="success-message">
                            <h3>✅ Message Sent!</h3>
                            <p>Thank you for contacting us. We'll get back to you soon.</p>
                            <button onClick=${() => setSuccess(false)}>
                                Send Another Message
                            </button>
                        </div>
                    `;
                }

                return html`
                    <form class="contact-form" onSubmit=${handleSubmit}>
                        <!-- Name Field -->
                        <div class="form-group">
                            <label for="name">
                                Name <span class="required">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                value=${formData.name}
                                onInput=${(e) => handleChange('name', e.target.value)}
                                onBlur=${() => handleBlur('name')}
                                class=${errors.name && touched.name ? 'error' : ''}
                                placeholder="Your name"
                            />
                            ${errors.name && touched.name && html`
                                <span class="error-message">❌ ${errors.name}</span>
                            `}
                        </div>

                        <!-- Email Field -->
                        <div class="form-group">
                            <label for="email">
                                Email <span class="required">*</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                value=${formData.email}
                                onInput=${(e) => handleChange('email', e.target.value)}
                                onBlur=${() => handleBlur('email')}
                                class=${errors.email && touched.email ? 'error' : ''}
                                placeholder="your@email.com"
                            />
                            ${errors.email && touched.email && html`
                                <span class="error-message">❌ ${errors.email}</span>
                            `}
                        </div>

                        <!-- Message Field -->
                        <div class="form-group">
                            <label for="message">
                                Message <span class="required">*</span>
                            </label>
                            <textarea
                                id="message"
                                value=${formData.message}
                                onInput=${(e) => handleChange('message', e.target.value)}
                                onBlur=${() => handleBlur('message')}
                                class=${errors.message && touched.message ? 'error' : ''}
                                placeholder="Your message..."
                                rows="5"
                            ></textarea>
                            <div class="char-count">
                                ${formData.message.length} / 500 characters
                            </div>
                            ${errors.message && touched.message && html`
                                <span class="error-message">❌ ${errors.message}</span>
                            `}
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            class="submit-btn"
                            disabled=${submitting}
                        >
                            ${submitting ? '⏳ Sending...' : '📧 Send Message'}
                        </button>
                    </form>
                `;
            }

            render(
                html`<${ContactForm} />`,
                document.getElementById('contact-form')
            );
        </script>
        <?php
    }
}
```

---

## Advanced: Password Strength Form

```javascript
function PasswordStrengthMeter({ password }) {
    const calculateStrength = (pwd) => {
        let strength = 0;
        
        if (pwd.length >= 8) strength++;
        if (pwd.length >= 12) strength++;
        if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) strength++;
        if (/\d/.test(pwd)) strength++;
        if (/[^a-zA-Z0-9]/.test(pwd)) strength++;
        
        return strength;
    };
    
    const strength = calculateStrength(password);
    const levels = ['Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    const colors = ['#f44336', '#ff9800', '#ffc107', '#8bc34a', '#4caf50'];
    
    return html`
        <div class="password-strength">
            <div class="strength-bar">
                ${[0, 1, 2, 3, 4].map(i => html`
                    <div
                        key=${i}
                        class="strength-segment"
                        style=${{
                            background: i < strength ? colors[strength - 1] : '#e0e0e0'
                        }}
                    ></div>
                `)}
            </div>
            ${password && html`
                <span style=${{ color: colors[strength - 1] }}>
                    ${levels[strength - 1]}
                </span>
            `}
        </div>
    `;
}
```

---

## Advanced: File Upload with Preview

```javascript
function FileUploadIsland() {
    const [files, setFiles] = useState([]);
    const [previews, setPreviews] = useState([]);
    
    const handleFileChange = (e) => {
        const selectedFiles = Array.from(e.target.files);
        setFiles(selectedFiles);
        
        // Generate previews
        selectedFiles.forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    setPreviews(prev => [...prev, {
                        name: file.name,
                        url: e.target.result,
                        size: file.size
                    }]);
                };
                reader.readAsDataURL(file);
            }
        });
    };
    
    const removeFile = (index) => {
        setFiles(files.filter((_, i) => i !== index));
        setPreviews(previews.filter((_, i) => i !== index));
    };
    
    return html`
        <div class="file-upload">
            <input
                type="file"
                multiple
                accept="image/*"
                onChange=${handleFileChange}
                id="file-input"
                style=${{ display: 'none' }}
            />
            
            <label for="file-input" class="upload-btn">
                📁 Choose Files
            </label>
            
            ${previews.length > 0 && html`
                <div class="preview-grid">
                    ${previews.map((preview, index) => html`
                        <div key=${index} class="preview-item">
                            <img src=${preview.url} alt=${preview.name} />
                            <div class="preview-info">
                                <span>${preview.name}</span>
                                <button onClick=${() => removeFile(index)}>✕</button>
                            </div>
                        </div>
                    `)}
                </div>
            `}
        </div>
    `;
}
```

---

## Advanced: Multi-Step Form

```javascript
function MultiStepForm() {
    const [step, setStep] = useState(1);
    const [formData, setFormData] = useState({
        // Step 1
        name: '',
        email: '',
        // Step 2
        address: '',
        city: '',
        // Step 3
        cardNumber: '',
        expiry: ''
    });
    
    const totalSteps = 3;
    
    const nextStep = () => setStep(s => Math.min(s + 1, totalSteps));
    const prevStep = () => setStep(s => Math.max(s - 1, 1));
    
    const progress = (step / totalSteps) * 100;
    
    return html`
        <div class="multi-step-form">
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div 
                    class="progress-fill"
                    style=${{ width: progress + '%' }}
                ></div>
            </div>
            
            <p class="step-indicator">Step ${step} of ${totalSteps}</p>
            
            <!-- Step Content -->
            ${step === 1 && html`
                <div class="step">
                    <h3>Personal Information</h3>
                    <input 
                        type="text"
                        placeholder="Name"
                        value=${formData.name}
                        onInput=${(e) => setFormData({...formData, name: e.target.value})}
                    />
                    <input 
                        type="email"
                        placeholder="Email"
                        value=${formData.email}
                        onInput=${(e) => setFormData({...formData, email: e.target.value})}
                    />
                </div>
            `}
            
            ${step === 2 && html`
                <div class="step">
                    <h3>Shipping Address</h3>
                    <input 
                        type="text"
                        placeholder="Address"
                        value=${formData.address}
                        onInput=${(e) => setFormData({...formData, address: e.target.value})}
                    />
                    <input 
                        type="text"
                        placeholder="City"
                        value=${formData.city}
                        onInput=${(e) => setFormData({...formData, city: e.target.value})}
                    />
                </div>
            `}
            
            ${step === 3 && html`
                <div class="step">
                    <h3>Payment</h3>
                    <input 
                        type="text"
                        placeholder="Card Number"
                        value=${formData.cardNumber}
                        onInput=${(e) => setFormData({...formData, cardNumber: e.target.value})}
                    />
                    <input 
                        type="text"
                        placeholder="MM/YY"
                        value=${formData.expiry}
                        onInput=${(e) => setFormData({...formData, expiry: e.target.value})}
                    />
                </div>
            `}
            
            <!-- Navigation -->
            <div class="form-nav">
                ${step > 1 && html`
                    <button onClick=${prevStep}>← Back</button>
                `}
                
                ${step < totalSteps ? html`
                    <button onClick=${nextStep} class="primary">Next →</button>
                ` : html`
                    <button class="primary">Complete</button>
                `}
            </div>
        </div>
    `;
}
```

---

## Best Practices

✅ **Progressive Enhancement** - Form works without JS  
✅ **Real-time Validation** - Immediate feedback  
✅ **Clear Error Messages** - Tell users what's wrong  
✅ **Loading States** - Show when submitting  
✅ **Success Feedback** - Confirm successful submission  
✅ **Accessibility** - Labels, ARIA, keyboard navigation  

---

**Next:** [State Management →](../STATE_MANAGEMENT.md)
