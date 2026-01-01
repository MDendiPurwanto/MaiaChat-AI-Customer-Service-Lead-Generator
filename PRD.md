# PRD: WordPress CS Assistant Plugin (MaiaRouter Integration)

## 1. Project Overview
The **WordPress CS Assistant Plugin** is a specialized tool designed to provide a premium AI-driven customer service interface for WordPress websites. It leverages **MaiaRouter** LLMs (specifically `maia/gemini-2.5-flash`) to handle inquiries, utilizing a multi-source Knowledge Base including manual context, website URLs, and document uploads.

## 2. Objectives
- Seamlessly integrate MaiaRouter LLM capabilities into WordPress.
- Provide a highly customizable, modern, and branded chat interface.
- Enable lead generation by capturing user data (Name & WhatsApp) before chat begins.
- Bridge AI assistance with human support via seamless WhatsApp handoff.
- Build a robust Knowledge Base from various sources (Manual, URL Scraping, Document Uploads).

## 3. Key Features (Implemented)

### 3.1. Admin Configuration (Backend)
- **MaiaRouter API Integration**: 
    - Full support for `api.maiarouter.ai`.
    - Configurable API Key and Model Selection.
- **Branding & UI Customization**:
    - Assistant Name, Welcome Message, and Primary Color.
    - Premium Header UI with status indicators.
- **Knowledge Base Management**:
    - **Manual Context**: Core brand identity and instructions.
    - **URL Knowledge Source**: Fetch content directly from any URL (HTML cleaning included).
    - **Document Upload**: Support for `.txt` and `.md` file extraction.
- **Tracking & Handoff Settings**:
    - WhatsApp Number for human agent redirection.
    - Customizable Handoff button wording.
    - Toggle for Lead Generation form.

### 3.2. Chat Widget (Frontend)
- **Premium Aesthetics**:
    - Floating Action Button (FAB) with custom animations.
    - Glassmorphism effects, smooth gradients, and modern gradients.
    - Clean, responsive modal design.
- **Smart Response Rendering**:
    - Robust Markdown support: Proper rendering of Bold, Headers, and Lists.
    - Automatic Link detection and formatting (Handles Markdown links `[text](url)` and raw URLs).
    - Clean numbered lists (fixed common regex issues).
- **Lead Capture Form**:
    - Modern full-width input fields for Name and WhatsApp.
    - Captured data is logged to `logs/leads.log` and included in chat tracking.

### 3.3. Tracking & Human Agency
- **Lead Logging**: Automatic server-side logging of captured customer data.
- **WhatsApp Handoff**: A specialized button appearing after conversation depth is reached, connecting users directly to a human agent.

## 4. Technical Stack
- **Backend**: WordPress PHP (Settings API, AJAX API), Media Library.
- **AI Engine**: MaiaRouter (OpenAI-compatible REST API).
- **Frontend**: Vanilla Javascript (Modern ES6+), Premium CSS3 (Variables, Flexbox, Animations).
- **Format Handling**: Custom regex-based Markdown & URL parser.

## 5. Current Implementation Status

| Feature | Status |
| :--- | :--- |
| MaiaRouter API Integration | ✅ Completed |
| Basic Settings Page | ✅ Completed |
| Frontend Chat UI (Premium) | ✅ Completed |
| URL Knowledge Fetching | ✅ Completed |
| Document Upload Knowledge | ✅ Completed |
| Lead Generation Form | ✅ Completed |
| WhatsApp Handoff | ✅ Completed |
| Markdown & Link Cleaning | ✅ Completed |
| S3 Storage Preparation | ⏳ Integrated / Optional |

## 6. Design Principles
- **Clarity**: Information should be easily digestible (clean lists, bold text).
- **Accessibility**: Large, touch-friendly buttons and clear contrast.
- **Brand Consistency**: Every UI element follows the user-defined primary color.
- **Modernity**: Avoid legacy styles; use shadows, rounded corners (14-20px), and smooth transitions.

---
*Updated: December 2025*
