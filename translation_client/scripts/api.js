const BASE = "/seai-01-group-project/translation_server/API";
const CHAIN_LANGUAGES = ["en", "ja", "ar", "fi", "sw", "hu", "ko", "en"];

let currentRoundId = null;
let currentHints = [];

class ApiError extends Error {
  constructor(message, code) {
    super(message);
    this.code = code;
  }
}

async function request(path, options = {}) {
  try {
    const response = await axios({
      url: `${BASE}${path}`,
      ...options,
    });

    return response.data;
  } catch (error) {
    if (error.response) {
      throw new ApiError(`Server error (${error.response.status})`, "http");
    }

    throw new ApiError("Network error - is the server running?", "network");
  }
}

function post(path, data = {}) {
  return request(path, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    data: new URLSearchParams(data),
  });
}

function idleState() {
  return {
    round_id: null,
    status: "idle",
    server_time: Date.now() / 1000,
    opens_at: null,
    closes_at: null,
    puzzle: null,
    chain_languages: CHAIN_LANGUAGES,
    hints_used: 0,
    hints: [],
    seed: null,
    chain: null,
    mangle_score: null,
  };
}

export const Api = {
  async register(name, email, password) {
    const response = await post("/users/add_user.php", { name, email, password });

    return {
      ok: Boolean(response.success),
      user: response.data,
      error: response.message,
    };
  },

  async login(email, password) {
    const response = await post("/users/get_user.php", { email, password });

    return {
      ok: Boolean(response.success),
      user: response.data,
      error: response.message,
    };
  },

  async getState() {
    if (currentRoundId == null) {
      return idleState();
    }

    const response = await request(
      `/rounds/get_round.php?round_id=${encodeURIComponent(currentRoundId)}`,
    );

    if (!response.success) {
      currentRoundId = null;
      return idleState();
    }

    const round = response.data;
    const serverTime = Date.now() / 1000;
    const remainingSeconds = Number(round.remaining_seconds || 0);
    const revealed = round.status === "closed";

    let chain = null;
    if (revealed) {
      chain = [
        { lang: "en", text: round.original_sentence },
        ...(round.steps || []).map((step) => ({
          lang: step.to_language,
          text: step.translated_text,
        })),
      ];
    }

    return {
      round_id: Number(round.id),
      status: revealed ? "revealed" : round.status,
      server_time: serverTime,
      opens_at: serverTime + remainingSeconds - 60,
      closes_at: serverTime + remainingSeconds,
      puzzle: round.final_translation,
      chain_languages: CHAIN_LANGUAGES,
      hints_used: currentHints.length,
      hints: currentHints,
      seed: revealed ? round.original_sentence : null,
      chain,
      mangle_score: revealed ? Number(round.score) : null,
    };
  },

  async newRound() {
    const response = await post("/rounds/create_round.php");

    if (response.success) {
      currentRoundId = Number(response.round_id);
      currentHints = [];
    }

    return {
      ok: Boolean(response.success),
      round_id: response.round_id,
      error: response.message,
    };
  },

  async submitGuess(roundId, guess) {
    const response = await post("/guesses/add_guess.php", {
      round_id: roundId,
      guess,
    });

    return {
      ok: Boolean(response.success),
      result: response.data?.result,
      similarity: response.data?.similarity,
      score: response.data?.final_score,
      error: response.message,
    };
  },

  async requestHint(roundId) {
    const response = await post("/hints/use_hint.php", {
      round_id: roundId,
    });

    if (!response.success) {
      return {
        ok: false,
        error: response.message === "no more hints left" ? "no_more_hints" : response.message,
      };
    }

    const hint = {
      index: Number(response.data.hint.step_number),
      lang: response.data.hint.to_language,
      text: response.data.hint.translated_text,
    };

    currentHints.push(hint);

    return {
      ok: true,
      hint,
      penalty: Number(response.data.points_penalty),
    };
  },

  async getHallOfFame(sort) {
    const phpSort = sort === "votes" ? "votes" : "score";
    const response = await request(
      `/hall_of_fame/get_entries.php?sort=${encodeURIComponent(phpSort)}`,
    );

    return {
      entries: (response.data || []).map((entry) => ({
        id: Number(entry.round_id),
        seed: entry.original_sentence,
        final: entry.final_translation,
        mangle_score: Number(entry.score),
        votes: Number(entry.votes),
      })),
    };
  },

  async upvote(entryId) {
    const response = await post("/votes/add_vote.php", {
      round_id: entryId,
    });

    return {
      ok: Boolean(response.success),
      error: response.message,
    };
  },
};

export { ApiError };
