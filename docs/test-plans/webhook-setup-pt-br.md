# Configuração do Webhook

Esta seção demonstra como configurar webhooks com seu e-commerce Prestashop. 

## 0. Registre o link do seu webhook na Woovi

Para encontrá-lo, acesso o seguinte menu **API/Plugins**

Na sequência clique no botão **Novo Webhook**

A tela abaixo deverá aparecer. 

O link a ser cadastrado será parecido com esse 
```
https://seuwebsiteaqui.com.br/woovi/webhook
```

Ele deverá ser digitado no campo  **URL**

![Registering webhook endpoint in woovi](./media/webhook-set-up-step-0.PNG "step 0")

Pronto! Webhook cadastrado.

## 1. Agora, os pedidos já podem ser atualizados pela Woovi

Depois da confirmação do pagamento, o status do pedido será alterado para **Pagamento Aceito**

![Order status updated in prestashop after webhook endpoint is triggered](./media/webhook-set-up-step-1-pt-br.PNG "step 1")