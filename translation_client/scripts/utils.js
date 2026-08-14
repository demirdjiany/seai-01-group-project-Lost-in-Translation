export function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export function formatClock(totalSeconds) {
  const s = Math.max(0, Math.ceil(totalSeconds));
  const m = Math.floor(s / 60);
  const rem = s % 60;
  return `${m}:${String(rem).padStart(2, "0")}`;
}

export function clamp(value, min, max) {
  return Math.min(max, Math.max(min, value));
}

export function mangleTier(score) {
  if (score >= 80) return "extreme";
  if (score >= 50) return "high";
  if (score >= 25) return "medium";
  return "mild";
}

const CONFETTI_COLORS = ["var(--accent)", "var(--good)", "var(--warn)", "var(--pop-1)", "var(--pop-3)"];

export function burstConfetti(originEl) {
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  const rect = originEl.getBoundingClientRect();
  const originX = rect.left + rect.width / 2;
  const originY = rect.top + rect.height / 2;

  for (let i = 0; i < 14; i++) {
    const angle = Math.random() * Math.PI * 2;
    const distance = 60 + Math.random() * 60;
    const piece = document.createElement("span");
    piece.className = "confetti-piece";
    piece.style.left = `${originX}px`;
    piece.style.top = `${originY}px`;
    piece.style.background = CONFETTI_COLORS[i % CONFETTI_COLORS.length];
    piece.style.setProperty("--dx", `${Math.cos(angle) * distance}px`);
    piece.style.setProperty("--dy", `${Math.sin(angle) * distance}px`);
    piece.style.setProperty("--rot", `${Math.random() * 360 - 180}deg`);
    piece.addEventListener("animationend", () => piece.remove());
    document.body.appendChild(piece);
  }
}
