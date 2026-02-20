/**
 * Landing Page - Prep4You
 * Interatividade da página de boas-vindas
 */

const WA = "5511952728711";

document.addEventListener("DOMContentLoaded", () => {
  initFormHandler();
});

/**
 * Inicializa o handler do formulário de contato
 */
function initFormHandler() {
  const form = document.getElementById("leadForm");

  if (!form) return;

  form.addEventListener("submit", handleFormSubmit);
}

/**
 * Processa o envio do formulário
 * @param {Event} e - Evento do submit
 */
function handleFormSubmit(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const leadData = {
    nome: String(formData.get("nome") || "").trim(),
    email: String(formData.get("email") || "").trim(),
    telefone: String(formData.get("telefone") || "").trim(),
    volume: String(formData.get("volume") || "").trim(),
    mensagem: String(formData.get("mensagem") || "").trim(),
  };

  if (!validateLeadData(leadData)) {
    alert("Por favor, preencha todos os campos obrigatórios.");
    return;
  }

  sendToWhatsApp(leadData);

  this.reset();
}

/**
 * Valida os dados do lead
 * @param {Object} data - Dados do lead
 * @returns {boolean} Se os dados são válidos
 */
function validateLeadData(data) {
  return data.nome && data.email && data.telefone && data.volume;
}

/**
 * Envia os dados para WhatsApp
 * @param {Object} data - Dados do lead
 */
function sendToWhatsApp(data) {
  const message = buildWhatsAppMessage(data);
  const url = `https://wa.me/${WA}?text=${encodeURIComponent(message)}`;

  window.open(url, "_blank", "noopener");
}

/**
 * Constrói a mensagem para WhatsApp
 * @param {Object} data - Dados do lead
 * @returns {string} Mensagem formatada
 */
function buildWhatsAppMessage(data) {
  const lines = [
    "Olá! Quero falar com o Prep4You.",
    "",
    `Nome: ${data.nome}`,
    `E-mail: ${data.email}`,
    `Telefone: ${data.telefone}`,
    `Volume mensal: ${data.volume}`,
    `Mensagem: ${data.mensagem || "-"}`,
    "",
    "Pode me passar uma estimativa? (Sei que a etiquetagem pode chegar a até R$ 0,65/un conforme volume.)",
  ];

  return lines.join("\n");
}

/**
 * Adiciona comportamento de scroll smooth para links internos (opcional)
 */
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href === "#" || href === "") return;

    e.preventDefault();
    const target = document.querySelector(href);

    if (target) {
      target.scrollIntoView({ behavior: "smooth" });
    }
  });
});
