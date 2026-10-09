<!DOCTYPE html>
<html lang="en">

<head>
  <title>AI Chat</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300&family=Syne:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #0b0d11;
      --surface: rgba(255, 255, 255, 0.04);
      --surface-hover: rgba(255, 255, 255, 0.07);
      --border: rgba(255, 255, 255, 0.08);
      --accent: #7effa0;
      --accent-dim: rgba(126, 255, 160, 0.15);
      --user-bg: rgba(126, 255, 160, 0.1);
      --user-border: rgba(126, 255, 160, 0.3);
      --bot-bg: rgba(255, 255, 255, 0.04);
      --bot-border: rgba(255, 255, 255, 0.1);
      --text: #e8eaf0;
      --text-muted: #6b7080;
      --text-dim: #3a3e4a;
      --radius: 16px;
      --radius-sm: 10px;
      --font-ui: 'Syne', sans-serif;
      --font-mono: 'DM Mono', monospace;
    }

    html, body { height: 100%; }

    body {
      font-family: var(--font-ui);
      background: var(--bg);
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 60% 50% at 20% 80%, rgba(126, 255, 160, 0.06) 0%, transparent 70%),
        radial-gradient(ellipse 50% 40% at 80% 20%, rgba(100, 130, 255, 0.05) 0%, transparent 70%);
      pointer-events: none;
    }

    body::after {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
      pointer-events: none;
      opacity: 0.4;
    }

    .chat-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 780px;
      margin: 0 auto;
      padding: 0;
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100vh;
      animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .chat-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 28px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }

    .header-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--accent);
      box-shadow: 0 0 10px var(--accent);
      animation: pulse 2.5s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--accent); }
      50%       { opacity: 0.5; box-shadow: 0 0 20px var(--accent); }
    }

    .header-title {
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-muted);
    }

    /* ── Model Selector ── */
    .model-selector-wrap {
      margin-left: auto;
      position: relative;
      display: flex;
      align-items: center;
    }

    .model-selector-btn {
      display: flex;
      align-items: center;
      gap: 6px;
      font-family: var(--font-mono);
      font-size: 10px;
      color: var(--accent);
      background: var(--accent-dim);
      border: 1px solid rgba(126, 255, 160, 0.2);
      padding: 4px 10px;
      border-radius: 20px;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;
      user-select: none;
    }

    .model-selector-btn:hover {
      background: rgba(126, 255, 160, 0.22);
      border-color: rgba(126, 255, 160, 0.35);
    }

    .model-selector-btn .chevron {
      width: 10px;
      height: 10px;
      fill: var(--accent);
      transition: transform 0.2s ease;
      flex-shrink: 0;
    }

    .model-selector-btn.open .chevron { transform: rotate(180deg); }

    /* ── Dropdown ── */
    .model-dropdown {
      display: none;
      flex-direction: column;
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      background: #161820;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      overflow: hidden;
      min-width: 260px;
      max-height: 420px;
      z-index: 100;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
    }

    .model-dropdown.open {
      display: flex;
      animation: dropIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes dropIn {
      from { opacity: 0; transform: translateY(-6px) scale(0.98); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .model-dropdown-header {
      font-family: var(--font-mono);
      font-size: 9px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-dim);
      padding: 10px 14px 6px;
      flex-shrink: 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .model-search-wrap {
      padding: 8px 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      flex-shrink: 0;
    }

    .model-search-wrap input {
      width: 100%;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 7px;
      color: var(--text);
      font-family: var(--font-mono);
      font-size: 11px;
      padding: 6px 10px;
      outline: none;
      transition: border-color 0.15s;
    }

    .model-search-wrap input:focus { border-color: rgba(126, 255, 160, 0.3); }
    .model-search-wrap input::placeholder { color: var(--text-dim); }

    .model-list { overflow-y: auto; flex: 1; }
    .model-list::-webkit-scrollbar { width: 8px; }
    .model-list::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
    .model-list::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
    .model-list::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.18); }

    .model-option {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 14px;
      cursor: pointer;
      transition: background 0.15s ease;
    }

    .model-option:hover { background: rgba(255, 255, 255, 0.05); }
    .model-option.active { background: var(--accent-dim); }

    .model-option-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

    .model-option-info { flex: 1; min-width: 0; }

    .model-option-name {
      font-size: 12px;
      font-weight: 600;
      color: var(--text);
      letter-spacing: 0.02em;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .model-option.active .model-option-name { color: var(--accent); }

    .model-option-desc {
      font-family: var(--font-mono);
      font-size: 10px;
      color: var(--text-muted);
      margin-top: 1px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .model-check { width: 12px; height: 12px; fill: var(--accent); opacity: 0; flex-shrink: 0; }
    .model-option.active .model-check { opacity: 1; }

    .model-count {
      font-family: var(--font-mono);
      font-size: 9px;
      letter-spacing: 0.08em;
      color: var(--text-dim);
      padding: 6px 14px;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      text-align: center;
      flex-shrink: 0;
    }

    .model-empty {
      padding: 16px;
      text-align: center;
      font-family: var(--font-mono);
      font-size: 11px;
      color: var(--text-dim);
    }

    /* ── Chat box ── */
    .chat-box {
      background: transparent;
      border: none;
      border-radius: 0;
      overflow: hidden;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    /* ── Messages ── */
    .messages {
      flex: 1;
      overflow-y: auto;
      padding: 32px 28px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      scroll-behavior: smooth;
    }

    .messages::-webkit-scrollbar { width: 4px; }
    .messages::-webkit-scrollbar-track { background: transparent; }
    .messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    /* ── Empty state ── */
    .empty-state {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: var(--text-dim);
      font-family: var(--font-mono);
      font-size: 12px;
      letter-spacing: 0.05em;
    }

    .empty-icon { font-size: 28px; opacity: 0.4; margin-bottom: 4px; }

    /* ── Message bubbles ── */
    .msg {
      max-width: 80%;
      animation: msgIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes msgIn {
      from { opacity: 0; transform: translateY(8px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .msg.user { align-self: flex-end; }
    .msg.bot  { align-self: flex-start; width: 100%; max-width: 100%; }

    .msg-label {
      font-family: var(--font-mono);
      font-size: 9px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom: 5px;
      opacity: 0.5;
    }

    .msg.user .msg-label { text-align: right; color: var(--accent); }
    .msg.bot  .msg-label { color: var(--text-muted); }

    .msg-bubble {
      padding: 11px 15px;
      border-radius: var(--radius-sm);
      font-size: 14px;
      line-height: 1.6;
      color: var(--text);
      word-break: break-word;
    }

    .msg.user .msg-bubble {
      background: var(--user-bg);
      border: 1px solid var(--user-border);
      border-bottom-right-radius: 4px;
      color: #d4ffe0;
    }

    .msg.bot .msg-bubble {
      background: var(--bot-bg);
      border: 1px solid var(--bot-border);
      border-bottom-left-radius: 4px;
    }

    /* ── DB Result Table ── */
    .result-table-wrap {
      overflow-x: auto;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: 2px;
    }

    .result-table {
      width: 100%;
      border-collapse: collapse;
      font-family: var(--font-mono);
      font-size: 12px;
    }

    .result-table thead tr {
      background: rgba(126, 255, 160, 0.08);
      border-bottom: 1px solid rgba(126, 255, 160, 0.2);
    }

    .result-table thead th {
      padding: 8px 12px;
      text-align: left;
      color: var(--accent);
      font-weight: 500;
      letter-spacing: 0.05em;
      white-space: nowrap;
    }

    .result-table tbody tr {
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: background 0.15s;
    }

    .result-table tbody tr:last-child { border-bottom: none; }
    .result-table tbody tr:hover { background: rgba(255, 255, 255, 0.04); }

    .result-table tbody td {
      padding: 7px 12px;
      color: var(--text);
      vertical-align: top;
      white-space: nowrap;
    }

    .result-table tbody td.null-val { color: var(--text-dim); font-style: italic; }

    .result-meta {
      font-family: var(--font-mono);
      font-size: 10px;
      color: var(--text-muted);
      margin-top: 7px;
      letter-spacing: 0.04em;
    }

    .result-no-data {
      color: var(--text-dim);
      font-family: var(--font-mono);
      font-size: 13px;
      font-style: italic;
    }

    .result-error {
      color: #ff8080;
      font-family: var(--font-mono);
      font-size: 13px;
    }

    /* ── Typing indicator ── */
    .typing-indicator {
      display: none;
      align-self: flex-start;
      max-width: 80px;
      animation: msgIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .typing-indicator.visible { display: flex; }

    .typing-bubble {
      background: var(--bot-bg);
      border: 1px solid var(--bot-border);
      border-bottom-left-radius: 4px;
      border-radius: var(--radius-sm);
      padding: 14px 18px;
      display: flex;
      gap: 5px;
      align-items: center;
    }

    .typing-bubble span {
      width: 5px;
      height: 5px;
      background: var(--text-muted);
      border-radius: 50%;
      animation: bounce 1.2s ease-in-out infinite;
    }

    .typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
    .typing-bubble span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes bounce {
      0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
      30%            { transform: translateY(-4px); opacity: 1; }
    }

    /* ── Divider ── */
    .divider { height: 1px; background: var(--border); }

    /* ── Input area ── */
    .input-area {
      display: flex;
      align-items: center;
      gap: 0;
      padding: 16px 28px 24px;
      background: transparent;
      flex-shrink: 0;
    }

    .input-box {
      flex: 1;
      display: flex;
      align-items: center;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 12px 16px;
      transition: border-color 0.2s;
    }

    .input-box:focus-within { border-color: rgba(126, 255, 160, 0.3); }

    #message {
      width: 100%;
      background: transparent;
      border: none;
      outline: none;
      font-family: var(--font-ui);
      font-size: 14px;
      color: var(--text);
      caret-color: var(--accent);
      padding: 6px 0;
    }

    #message::placeholder { color: var(--text-dim); font-size: 13px; }

    .send-btn {
      flex-shrink: 0;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: var(--accent);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      margin-left: 10px;
    }

    .send-btn:hover { background: #a8ffc0; transform: scale(1.05); box-shadow: 0 0 16px rgba(126, 255, 160, 0.4); }
    .send-btn:active { transform: scale(0.96); }
    .send-btn svg { width: 15px; height: 15px; fill: #0b0d11; }
    .send-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ── Footer ── */
    .chat-footer {
      text-align: center;
      font-family: var(--font-mono);
      font-size: 10px;
      color: var(--text-dim);
      letter-spacing: 0.05em;
      padding-bottom: 10px;
      flex-shrink: 0;
    }

    /* ── Message action buttons ── */
    .msg-actions {
      display: flex;
      gap: 6px;
      margin-top: 6px;
      opacity: 0;
      transition: opacity 0.18s ease;
    }

    .msg:hover .msg-actions { opacity: 1; }
    .msg.user .msg-actions { justify-content: flex-end; }
    .msg.bot  .msg-actions { justify-content: flex-start; }

    .action-btn {
      display: flex;
      align-items: center;
      gap: 5px;
      font-family: var(--font-mono);
      font-size: 10px;
      letter-spacing: 0.05em;
      color: var(--text-muted);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 4px 9px;
      cursor: pointer;
      transition: all 0.15s ease;
      user-select: none;
    }

    .action-btn:hover { color: var(--text); background: var(--surface-hover); border-color: rgba(255, 255, 255, 0.15); }

    .action-btn.copied {
      color: var(--accent);
      border-color: rgba(126, 255, 160, 0.3);
      background: var(--accent-dim);
    }

    .action-btn svg { width: 11px; height: 11px; fill: currentColor; flex-shrink: 0; }
  </style>
</head>

<body>

  <div class="chat-wrap">
    <div class="chat-header">
      <div class="header-dot"></div>
      <span class="header-title">AI Assistant</span>

      <div class="model-selector-wrap">
        <button class="model-selector-btn" id="modelSelectorBtn" onclick="toggleModelDropdown()">
          <span id="modelBtnLabel">Gemma 3 4B</span>
          <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
        </button>

        <div class="model-dropdown" id="modelDropdown">
          <div class="model-dropdown-header">Select model</div>
          <div class="model-search-wrap">
            <input type="text" id="modelSearch" placeholder="Search models…" oninput="filterModels(this.value)" autocomplete="off">
          </div>
          <div class="model-list" id="modelList"></div>
          <div class="model-count" id="modelCount"></div>
        </div>
      </div>
    </div>

    <div class="chat-box">
      <div class="messages" id="messages">
        <div class="empty-state" id="emptyState">
          <div class="empty-icon">⬡</div>
          <span>Ask about your data</span>
        </div>
        <div class="typing-indicator" id="typingIndicator">
          <div class="typing-bubble">
            <span></span><span></span><span></span>
          </div>
        </div>
      </div>

      <div class="divider"></div>

      <div class="input-area">
        <div class="input-box">
          <input type="text" id="message" placeholder="Type a message…" autocomplete="off">
          <button class="send-btn" id="sendBtn" onclick="sendMessage()" title="Send">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="chat-footer">powered by claude · end-to-end · secure</div>
  </div>

  <script>
    // ════════════════════════════════════════════════════════════
    //  MODEL DATA
    // ════════════════════════════════════════════════════════════
    const MODEL_LIST = [
      { id: "google/gemma-3-4b-it:free",                             label: "Gemma 3 4B",                    desc: "Google · Fast & efficient",           color: "#b57bff" },
      { id: "google/gemma-3-12b-it:free",                            label: "Gemma 3 12B",                   desc: "Google · Mid-range",                  color: "#b57bff" },
      { id: "google/gemma-3-27b-it:free",                            label: "Gemma 3 27B",                   desc: "Google · Large scale",                color: "#b57bff" },
      { id: "google/gemma-3-1b:free",                                label: "Gemma 3 1.25B",                 desc: "Google · Ultra compact",              color: "#b57bff" },
      { id: "google/gemma-2-27b-it:free",                            label: "Gemma 2 27B",                   desc: "Google · High capability",            color: "#b57bff" },
      { id: "google/gemma-2-9b-it:free",                             label: "Gemma 2 9B",                    desc: "Google · Balanced",                   color: "#b57bff" },
      { id: "google/gemma-2-2b-it:free",                             label: "Gemma 2 2B",                    desc: "Google · Lightweight",                color: "#b57bff" },
      { id: "qwen/qwen3-coder:free",                                 label: "Qwen3 Coder",                   desc: "Qwen · Best for coding",              color: "#fbbf24" },
      { id: "qwen/qwen3-235b-a22b:free",                             label: "Qwen3 235B A22B",               desc: "Qwen · Flagship model",               color: "#ff9f7e" },
      { id: "qwen/qwen3-30b-a3b:free",                               label: "Qwen3 30B A3B",                 desc: "Qwen · MoE efficient",                color: "#ff9f7e" },
      { id: "qwen/qwen3-14b:free",                                   label: "Qwen3 14B",                     desc: "Qwen · Mid-range instruct",           color: "#ff9f7e" },
      { id: "qwen/qwen3-8b:free",                                    label: "Qwen3 8B",                      desc: "Qwen · Compact instruct",             color: "#ff9f7e" },
      { id: "qwen/qwen3-4b:free",                                    label: "Qwen3 4B",                      desc: "Qwen · Fast & small",                 color: "#ff9f7e" },
      { id: "qwen/qwen2.5-72b-instruct:free",                        label: "Qwen2.5 72B Instruct",          desc: "Qwen · Powerful general purpose",     color: "#ff9f7e" },
      { id: "qwen/qwen2.5-vl-72b-instruct:free",                     label: "Qwen2.5 VL 72B Instruct",       desc: "Qwen · Vision-language",              color: "#ff9f7e" },
      { id: "qwen/qwen2.5-vl-7b-instruct:free",                      label: "Qwen2.5 VL 7B Instruct",        desc: "Qwen · Compact vision-language",      color: "#ff9f7e" },
      { id: "qwen/qwq-32b:free",                                     label: "QwQ 32B",                       desc: "Qwen · Deep reasoning",               color: "#f59e0b" },
      { id: "meta-llama/llama-4-maverick:free",                      label: "Llama 4 Maverick",              desc: "Meta · Maverick MoE",                 color: "#fb923c" },
      { id: "meta-llama/llama-4-scout:free",                         label: "Llama 4 Scout",                 desc: "Meta · Next-gen multimodal",          color: "#fb923c" },
      { id: "meta-llama/llama-3.3-70b-instruct:free",                label: "Llama 3.3 70B Instruct",        desc: "Meta · Powerful instruct",            color: "#fb923c" },
      { id: "meta-llama/llama-3.2-90b-vision-instruct:free",         label: "Llama 3.2 90B Vision",          desc: "Meta · Large vision model",           color: "#fb923c" },
      { id: "meta-llama/llama-3.2-11b-vision-instruct:free",         label: "Llama 3.2 11B Vision",          desc: "Meta · Vision-language",              color: "#fb923c" },
      { id: "meta-llama/llama-3.2-3b-instruct:free",                 label: "Llama 3.2 3B Instruct",         desc: "Meta · Ultra compact",                color: "#fb923c" },
      { id: "meta-llama/llama-3.1-8b-instruct:free",                 label: "Llama 3.1 8B Instruct",         desc: "Meta · Compact & fast",               color: "#fb923c" },
      { id: "openai/gpt-oss-120b:free",                              label: "GPT-OSS 120B",                  desc: "OpenAI · Open Source Series",         color: "#7effa0" },
      { id: "openai/gpt-oss-20b:free",                               label: "GPT-OSS 20B",                   desc: "OpenAI · Compact open source",        color: "#7effa0" },
      { id: "nvidia/llama-3.3-nemotron-super-49b-v1:free",           label: "Nemotron Super 49B",            desc: "NVIDIA · Reasoning powerhouse",       color: "#76e4a0" },
      { id: "nvidia/llama-3.1-nemotron-70b-instruct:free",           label: "Nemotron 70B Instruct",         desc: "NVIDIA · High performance",           color: "#76e4a0" },
      { id: "nvidia/llama-3.1-nemotron-nano-8b-v1:free",             label: "Nemotron Nano 8B V1",           desc: "NVIDIA · Ultra compact",              color: "#76e4a0" },
      { id: "nvidia/nemotron-nano-65b-v1:free",                      label: "Nemotron Nano 65B V1",          desc: "NVIDIA · Large nano",                 color: "#76e4a0" },
      { id: "nvidia/nemotron-3-nano-3b-a3b:free",                    label: "Nemotron 3 Nano 3B A3B",        desc: "NVIDIA · Tiny MoE",                   color: "#76e4a0" },
      { id: "nvidia/llava-nemotron-embod-vl-10-v2:free",             label: "Llava Nemotron EmbodVL V2",     desc: "NVIDIA · Embodied vision",            color: "#76e4a0" },
      { id: "deepseek/deepseek-r1:free",                             label: "DeepSeek R1",                   desc: "DeepSeek · Chain-of-thought",         color: "#38bdf8" },
      { id: "deepseek/deepseek-r1-distill-llama-70b:free",           label: "DeepSeek R1 Distill 70B",       desc: "DeepSeek · Distilled reasoning",      color: "#38bdf8" },
      { id: "deepseek/deepseek-chat-v3-0324:free",                   label: "DeepSeek Chat V3",              desc: "DeepSeek · General chat",             color: "#38bdf8" },
      { id: "deepseek/deepseek-prover-v2:free",                      label: "DeepSeek Prover V2",            desc: "DeepSeek · Math & proofs",            color: "#38bdf8" },
      { id: "mistralai/mistral-7b-instruct:free",                    label: "Mistral 7B Instruct",           desc: "Mistral · Fast instruct",             color: "#f97316" },
      { id: "mistralai/mistral-small-3.1-24b-instruct:free",         label: "Mistral Small 3.1 24B",         desc: "Mistral · Balanced performance",      color: "#f97316" },
      { id: "mistralai/devstral-small:free",                         label: "Devstral Small",                desc: "Mistral · Developer focused",         color: "#f97316" },
      { id: "stepfun/step-3.5-flash:free",                           label: "Step 3.5 Flash",                desc: "StepFun · Fast & balanced",           color: "#7eb8ff" },
      { id: "stepfun/step-1.5v-mini:free",                           label: "Step 1.5V Mini",                desc: "StepFun · Vision-language",           color: "#7eb8ff" },
      { id: "arcee-ai/arcee-trinity-large-preview:free",             label: "Arcee Trinity Large Preview",   desc: "Arcee AI · Large preview",            color: "#f472b6" },
      { id: "arcee-ai/arcee-trinity-mini:free",                      label: "Arcee Trinity Mini",            desc: "Arcee AI · Lightweight",              color: "#f472b6" },
      { id: "minimax/minimax-m1:free",                               label: "MiniMax M1",                    desc: "MiniMax · Multimodal reasoning",      color: "#60a5fa" },
      { id: "minimax/minimax-m2.5:free",                             label: "MiniMax M2.5",                  desc: "MiniMax · Enhanced multimodal",       color: "#60a5fa" },
      { id: "liquid/lfm-7b:free",                                    label: "LFM 7B",                        desc: "LiquidAI · Efficient LFM",            color: "#a78bfa" },
      { id: "liquidai/lfm2.5-1.23-thinking:free",                    label: "LFM2.5-1.23-Thinking",          desc: "LiquidAI · Deep reasoning",           color: "#a78bfa" },
      { id: "liquidai/lfm2.5-1.23-instruct:free",                    label: "LFM2.5-1.23-Instruct",          desc: "LiquidAI · Instruction tuned",        color: "#a78bfa" },
      { id: "zai/clm-4.5-ai:free",                                   label: "Zai CLM 4.5 AI",                desc: "Zai · Efficient model",               color: "#34d399" },
      { id: "vanilco/uncensored:free",                               label: "Vanilco Uncensored",            desc: "Vanilco · No restrictions",           color: "#f87171" },
      { id: "tngtech/deepseek-r1t-chimera:free",                     label: "DeepSeek R1T Chimera",          desc: "TNG Tech · Hybrid reasoning",         color: "#e879f9" },
      { id: "moonshotai/moonlight-16b-a3b-instruct:free",            label: "Moonlight 16B A3B",             desc: "MoonshotAI · MoE instruct",           color: "#818cf8" },
    ];

    // ════════════════════════════════════════════════════════════
    //  MODEL SELECTOR
    // ════════════════════════════════════════════════════════════
    let selectedModel = MODEL_LIST[0].id;
    let filteredModels = [...MODEL_LIST];

    function renderModels(list) {
      const container = document.getElementById('modelList');
      const countEl   = document.getElementById('modelCount');

      if (list.length === 0) {
        container.innerHTML = '<div class="model-empty">No models found</div>';
        countEl.textContent  = '0 models';
        return;
      }

      container.innerHTML = list.map(m => `
        <div class="model-option ${m.id === selectedModel ? 'active' : ''}" onclick="selectModel('${m.id}')">
          <div class="model-option-dot" style="background:${m.color};"></div>
          <div class="model-option-info">
            <div class="model-option-name">${m.label}</div>
            <div class="model-option-desc">${m.desc}</div>
          </div>
          <svg class="model-check" viewBox="0 0 24 24">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
          </svg>
        </div>
      `).join('');

      countEl.textContent = `${list.length} free model${list.length !== 1 ? 's' : ''}`;
    }

    function selectModel(id) {
      selectedModel = id;
      const found = MODEL_LIST.find(m => m.id === id);
      if (found) document.getElementById('modelBtnLabel').textContent = found.label;
      renderModels(filteredModels);
      closeModelDropdown();
    }

    function filterModels(query) {
      const q = query.toLowerCase().trim();
      filteredModels = q
        ? MODEL_LIST.filter(m =>
            m.label.toLowerCase().includes(q) ||
            m.desc.toLowerCase().includes(q)  ||
            m.id.toLowerCase().includes(q))
        : [...MODEL_LIST];
      renderModels(filteredModels);
    }

    function toggleModelDropdown() {
      const btn      = document.getElementById('modelSelectorBtn');
      const dropdown = document.getElementById('modelDropdown');
      if (dropdown.classList.contains('open')) {
        closeModelDropdown();
      } else {
        btn.classList.add('open');
        dropdown.classList.add('open');
        const search = document.getElementById('modelSearch');
        search.value  = '';
        filteredModels = [...MODEL_LIST];
        renderModels(filteredModels);
        setTimeout(() => search.focus(), 60);
      }
    }

    function closeModelDropdown() {
      document.getElementById('modelSelectorBtn').classList.remove('open');
      document.getElementById('modelDropdown').classList.remove('open');
    }

    document.addEventListener('click', function (e) {
      if (!document.querySelector('.model-selector-wrap').contains(e.target)) {
        closeModelDropdown();
      }
    });

    renderModels(MODEL_LIST);

    // ════════════════════════════════════════════════════════════
    //  CHAT
    // ════════════════════════════════════════════════════════════
    const messagesDiv    = document.getElementById('messages');
    const input          = document.getElementById('message');
    const sendBtn        = document.getElementById('sendBtn');
    const typingIndicator = document.getElementById('typingIndicator');
    const emptyState     = document.getElementById('emptyState');

    // ── Render reply: table / kv / empty / plain ──
    function renderReplyContent(reply) {

      // Array of objects → full table
      if (Array.isArray(reply) && reply.length > 0 && typeof reply[0] === 'object' && reply[0] !== null) {
        const keys = Object.keys(reply[0]);
        const headCells = keys.map(k => `<th>${escHtml(k)}</th>`).join('');
        const bodyRows  = reply.map(row =>
          `<tr>${keys.map(k => {
            const v      = row[k];
            const isNull = v === null || v === undefined;
            return `<td class="${isNull ? 'null-val' : ''}">${isNull ? 'null' : escHtml(String(v))}</td>`;
          }).join('')}</tr>`
        ).join('');

        return `
          <div class="result-table-wrap">
            <table class="result-table">
              <thead><tr>${headCells}</tr></thead>
              <tbody>${bodyRows}</tbody>
            </table>
          </div>
          <div class="result-meta">${reply.length} row${reply.length !== 1 ? 's' : ''} returned</div>`;
      }

      // Single object → key / value table
      if (reply && typeof reply === 'object' && !Array.isArray(reply)) {
        const entries   = Object.entries(reply);
        const bodyRows  = entries.map(([k, v]) => {
          const isNull = v === null || v === undefined;
          return `<tr>
            <td>${escHtml(k)}</td>
            <td class="${isNull ? 'null-val' : ''}">${isNull ? 'null' : escHtml(String(v))}</td>
          </tr>`;
        }).join('');

        return `
          <div class="result-table-wrap">
            <table class="result-table">
              <thead><tr><th>column</th><th>value</th></tr></thead>
              <tbody>${bodyRows}</tbody>
            </table>
          </div>
          <div class="result-meta">1 row returned</div>`;
      }

      // Empty array
      if (Array.isArray(reply) && reply.length === 0) {
        return `<span class="result-no-data">No data returned</span>`;
      }

      // Plain string / number / fallback
      return `<span>${escHtml(String(reply))}</span>`;
    }

    function escHtml(str) {
      return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function makeCopyBtn(text) {
      const btn = document.createElement('button');
      btn.className = 'action-btn copy-btn';
      btn.title     = 'Copy';
      btn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>Copy`;
      btn.addEventListener('click', () => {
        const copyText = typeof text === 'string' ? text : JSON.stringify(text, null, 2);
        navigator.clipboard.writeText(copyText).then(() => {
          btn.classList.add('copied');
          btn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Copied`;
          setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>Copy`;
          }, 2000);
        });
      });
      return btn;
    }

    function appendMessage(content, role) {
      if (emptyState) emptyState.style.display = 'none';

      const msg = document.createElement('div');
      msg.className = `msg ${role}`;

      const label = document.createElement('div');
      label.className   = 'msg-label';
      label.textContent = role === 'user' ? 'you' : 'assistant';

      const bubble = document.createElement('div');
      bubble.className = 'msg-bubble';

      if (role === 'bot') {
        bubble.innerHTML = renderReplyContent(content);
      } else {
        bubble.textContent = content;
      }

      const actions = document.createElement('div');
      actions.className = 'msg-actions';
      actions.appendChild(makeCopyBtn(content));

      if (role === 'user') {
        const resendBtn = document.createElement('button');
        resendBtn.className = 'action-btn resend-btn';
        resendBtn.title     = 'Send again';
        resendBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>Resend`;
        resendBtn.addEventListener('click', () => submitMessage(content));
        actions.appendChild(resendBtn);
      }

      msg.appendChild(label);
      msg.appendChild(bubble);
      msg.appendChild(actions);
      messagesDiv.insertBefore(msg, typingIndicator);
      scrollToBottom();
    }

    function showTyping()    { typingIndicator.classList.add('visible');    scrollToBottom(); }
    function hideTyping()    { typingIndicator.classList.remove('visible'); }
    function scrollToBottom(){ messagesDiv.scrollTop = messagesDiv.scrollHeight; }

    function setLoading(val) {
      sendBtn.disabled = val;
      input.disabled   = val;
    }

    function sendMessage() {
      const message = input.value.trim();
      if (!message) return;
      input.value = '';
      submitMessage(message);
    }

    function submitMessage(message) {
      appendMessage(message, 'user');
      setLoading(true);
      showTyping();

      fetch('/ai-chat', {
        method:  'POST',
        headers: {
          'Content-Type':  'application/json',
          'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message, model: selectedModel })
      })
        .then(res => res.json())
        .then(data => {
          hideTyping();
          // Pass data.reply raw — renderReplyContent handles all types
          appendMessage(data.reply, 'bot');
        })
        .catch(() => {
          hideTyping();
          appendMessage('Something went wrong. Please try again.', 'bot');
        })
        .finally(() => {
          setLoading(false);
          input.focus();
        });
    }

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    input.focus();
  </script>
</body>
</html>