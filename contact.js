// netlify/functions/contact.js
const fetch = require('node-fetch');

exports.handler = async function(event, context) {
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            body: JSON.stringify({ success: false, message: 'Method not allowed' })
        };
    }

    try {
        const data = JSON.parse(event.body);
        const { name, email, subject, message } = data;

        // Validation
        if (!name || !email || !subject || !message) {
            return {
                statusCode: 400,
                body: JSON.stringify({ success: false, message: 'All fields are required' })
            };
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return {
                statusCode: 400,
                body: JSON.stringify({ success: false, message: 'Invalid email address' })
            };
        }

        // Log the submission
        console.log('📧 New contact form submission:');
        console.log('Name:', name);
        console.log('Email:', email);
        console.log('Subject:', subject);
        console.log('Message:', message);

        // Send email notification
        await sendEmailNotification({
            name,
            email,
            subject,
            message,
            ip: event.headers['client-ip'] || 'Unknown',
            timestamp: new Date().toISOString()
        });

        return {
            statusCode: 200,
            body: JSON.stringify({ 
                success: true, 
                message: 'Thank you! Your message has been sent successfully. I will get back to you soon.' 
            })
        };

    } catch (error) {
        console.error('Error processing form:', error);
        return {
            statusCode: 500,
            body: JSON.stringify({ success: false, message: 'Internal server error. Please try again.' })
        };
    }
};

async function sendEmailNotification(formData) {
    try {
        // Using EmailJS
        const response = await fetch('https://api.emailjs.com/api/v1.0/email/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                service_id: process.env.EMAILJS_SERVICE_ID,
                template_id: process.env.EMAILJS_TEMPLATE_ID,
                user_id: process.env.EMAILJS_USER_ID,
                template_params: {
                    from_name: formData.name,
                    from_email: formData.email,
                    subject: formData.subject,
                    message: formData.message,
                    to_email: 'jlugaho@asu.edu',
                    ip_address: formData.ip,
                    timestamp: formData.timestamp
                }
            })
        });

        if (response.ok) {
            console.log('✅ Email notification sent successfully');
            return { success: true };
        } else {
            throw new Error(`Email failed: ${response.status}`);
        }

    } catch (error) {
        console.error('❌ Email notification failed:', error.message);
        return { success: false, error: error.message };
    }
}