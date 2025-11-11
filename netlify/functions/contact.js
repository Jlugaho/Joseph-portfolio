// netlify/functions/contact.js - DEBUG VERSION
const fetch = require('node-fetch');

exports.handler = async function(event, context) {
    console.log('🚀 Contact function started');
    
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

        console.log('📧 New contact form submission:');
        console.log('Name:', name);
        console.log('Email:', email);
        console.log('Subject:', subject);
        console.log('Message:', message);

        // Debug: Check if environment variables exist
        console.log('🔧 Environment variables check:');
        console.log('EMAILJS_SERVICE_ID exists:', !!process.env.EMAILJS_SERVICE_ID);
        console.log('EMAILJS_TEMPLATE_ID exists:', !!process.env.EMAILJS_TEMPLATE_ID);
        console.log('EMAILJS_USER_ID exists:', !!process.env.EMAILJS_USER_ID);

        // Send email notification
        const emailResult = await sendEmailNotification({
            name,
            email,
            subject,
            message,
            ip: event.headers['client-ip'] || 'Unknown',
            timestamp: new Date().toISOString()
        });

        if (emailResult.success) {
            console.log('✅ Email notification sent successfully');
        } else {
            console.log('❌ Email notification failed:', emailResult.error);
            // Don't fail the request - still return success to user
        }

        return {
            statusCode: 200,
            body: JSON.stringify({ 
                success: true, 
                message: 'Thank you! Your message has been sent successfully. I will get back to you soon.' 
            })
        };

    } catch (error) {
        console.error('💥 Error processing form:', error);
        return {
            statusCode: 500,
            body: JSON.stringify({ success: false, message: 'Internal server error. Please try again.' })
        };
    }
};

async function sendEmailNotification(formData) {
    try {
        console.log('📤 Attempting to send email via EmailJS...');
        
        // Verify we have all required environment variables
        if (!process.env.EMAILJS_SERVICE_ID || 
            !process.env.EMAILJS_TEMPLATE_ID || 
            !process.env.EMAILJS_USER_ID) {
            throw new Error('Missing EmailJS environment variables');
        }

        const emailData = {
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
        };

        console.log('📨 Sending to EmailJS with data:', {
            service_id: process.env.EMAILJS_SERVICE_ID ? 'SET' : 'MISSING',
            template_id: process.env.EMAILJS_TEMPLATE_ID ? 'SET' : 'MISSING',
            user_id: process.env.EMAILJS_USER_ID ? 'SET' : 'MISSING'
        });

        const response = await fetch('https://api.emailjs.com/api/v1.0/email/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(emailData)
        });

        console.log('📩 EmailJS response status:', response.status);
        
        if (response.ok) {
            console.log('✅ EmailJS request successful');
            return { success: true };
        } else {
            const errorText = await response.text();
            console.log('❌ EmailJS failed with status:', response.status, 'Response:', errorText);
            throw new Error(`EmailJS API error: ${response.status} - ${errorText}`);
        }

    } catch (error) {
        console.error('💥 Email notification error:', error.message);
        return { success: false, error: error.message };
    }
}