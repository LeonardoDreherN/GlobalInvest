(() => {
  const buttons = document.querySelectorAll("[data-mp-product]");
  if (!buttons.length) return;

  const checkoutEndpoint = "/api/mercadopago/create-preference.php";

  buttons.forEach((button) => {
    button.addEventListener("click", async () => {
      if (button.dataset.loading === "true") return;
      const originalLabel = button.textContent;
      button.dataset.loading = "true";
      button.disabled = true;
      button.textContent = "Abrindo pagamento…";

      try {
        const response = await fetch(checkoutEndpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            product: button.dataset.mpProduct,
            quantity: Number(button.dataset.mpQuantity || 1),
          }),
        });
        const result = await response.json();
        if (!response.ok || !result.checkout_url) {
          throw new Error(result.error || "Não foi possível iniciar o pagamento.");
        }
        window.location.assign(result.checkout_url);
      } catch (error) {
        const isPreview = window.location.hostname.endsWith("chatgpt.site");
        window.alert(
          isPreview
            ? "O checkout será ativado na Hostinger após inserir as credenciais do Mercado Pago."
            : error.message,
        );
        button.disabled = false;
        button.dataset.loading = "false";
        button.textContent = originalLabel;
      }
    });
  });
})();
