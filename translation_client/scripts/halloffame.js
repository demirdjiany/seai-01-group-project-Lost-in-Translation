import { Api, ApiError } from "./api.js";
import { mangleTier } from "./utils.js";

const MEDALS = ["🥇 ", "🥈 ", "🥉 "];

export class HallOfFameController {
  constructor(root) {
    this.root = root;
    this.listEl = root.querySelector("#fame-list");
    this.emptyEl = root.querySelector("#fame-empty");
    this.sortSelect = root.querySelector("#fame-sort");
    this.template = document.getElementById("tmpl-fame-card");

    this.sortSelect.addEventListener("change", () => this.load());
  }

  async load() {
    try {
      const { entries } = await Api.getHallOfFame(this.sortSelect.value);
      this._render(entries);
    } catch (error) {
      this.listEl.innerHTML = "";
      this.emptyEl.hidden = false;
      this.emptyEl.textContent =
        error instanceof ApiError ? error.message : "Couldn't load the Hall of Fame.";
    }
  }

  _render(entries) {
    this.listEl.innerHTML = "";
    this.emptyEl.hidden = entries.length > 0;
    this.emptyEl.textContent = "Nothing here yet - mangle a sentence to claim a spot.";

    entries.forEach((entry, index) => {
      const node = this.template.content.firstElementChild.cloneNode(true);
      const medal = MEDALS[index] || "";
      node.querySelector(".fame-seed").textContent = `${medal}${entry.seed}`;
      node.querySelector(".fame-final").textContent = entry.final;

      const mangleEl = node.querySelector(".fame-mangle-score");
      mangleEl.textContent = Math.round(entry.mangle_score);
      mangleEl.dataset.tier = mangleTier(entry.mangle_score);

      const voteBtn = node.querySelector(".fame-upvote");
      const votesEl = node.querySelector(".fame-votes");
      votesEl.textContent = entry.votes;

      voteBtn.addEventListener("click", () => this._upvote(entry.id, voteBtn, votesEl));
      this.listEl.appendChild(node);
    });
  }

  async _upvote(entryId, voteBtn, votesEl) {
    voteBtn.disabled = true;

    try {
      const response = await Api.upvote(entryId);

      if (!response.ok) {
        voteBtn.disabled = false;
        return;
      }

      votesEl.textContent = Number(votesEl.textContent) + 1;
      voteBtn.classList.add("is-voted");
    } catch {
      voteBtn.disabled = false;
    }
  }
}
