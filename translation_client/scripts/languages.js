const LANGUAGES = {
  en: { name: "English", flag: "🇬🇧" },
  ja: { name: "Japanese", flag: "🇯🇵" },
  ar: { name: "Arabic", flag: "🇸🇦" },
  fi: { name: "Finnish", flag: "🇫🇮" },
  sw: { name: "Swahili", flag: "🇰🇪" },
  hu: { name: "Hungarian", flag: "🇭🇺" },
  ko: { name: "Korean", flag: "🇰🇷" },
  fr: { name: "French", flag: "🇫🇷" },
  es: { name: "Spanish", flag: "🇪🇸" },
  it: { name: "Italian", flag: "🇮🇹" },
  de: { name: "German", flag: "🇩🇪" },
  zh: { name: "Chinese", flag: "🇨🇳" },
  ru: { name: "Russian", flag: "🇷🇺" },
  pt: { name: "Portuguese", flag: "🇵🇹" },
  nl: { name: "Dutch", flag: "🇳🇱" },
  pl: { name: "Polish", flag: "🇵🇱" },
  tr: { name: "Turkish", flag: "🇹🇷" },
  vi: { name: "Vietnamese", flag: "🇻🇳" },
  th: { name: "Thai", flag: "🇹🇭" },
  he: { name: "Hebrew", flag: "🇮🇱" },
  el: { name: "Greek", flag: "🇬🇷" },
  hi: { name: "Hindi", flag: "🇮🇳" },
  id: { name: "Indonesian", flag: "🇮🇩" },
  cs: { name: "Czech", flag: "🇨🇿" },
  sv: { name: "Swedish", flag: "🇸🇪" },
};

export function langMeta(code) {
  return LANGUAGES[code] || { name: code.toUpperCase(), flag: "🌐" };
}
