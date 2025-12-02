# 📘 AI-Powered French ↔ English Glossary (PHP + MySQL + Gemini)

**AI Glossary** is a lightweight PHP + MySQL MVP that provides instant French ↔ English translations and definition sentences.  
The app first checks the local **MySQL terms table**, and if the word is missing, it calls **Gemini (gemini-2.5-flash)** via API, displays the result, and automatically stores it for future use.

The application includes an **admin dashboard**, a **feedback system**, and a modern, code-editor-style UI with tabs, theme toggle, and animated loader.

---

## 🚀 Features

### 📚 AI Translation & Glossary Engine
- Local lookup from MySQL terms table  
- If not found → call **Gemini API** for:
  - Translation  
  - Definition sentence  
- Save new terms automatically for next use  
- Fast lookup with live search (admin)

### 🔐 Admin Dashboard
- Admin login (`admin / admin123` seeded)  
- Create, update, delete glossary terms  
- Search and filter terms  
- Minimal MVC structure (controllers, models, views)

### 📨 User Feedback System
- Feedback page using **Formspree**  
- Submits to: `https://formspree.io/f/mblnyonz`  
- No backend logic needed — instant email collection

### 🎨 UI & Interactions
- Code-editor themed layout with:
  - Tabbed pages (Glossary / Dashboard / Feedback)  
  - Dark / Light mode lamp toggle  
  - Animated EVA loader for API calls  
- Tailwind CSS (via CDN) + custom CSS variables  
- Vanilla JS for UI logic and admin CRUD actions  

---

# 💻 Tech Stack

## 🖥️ Backend
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

- **PHP 8+** runtime  
- Minimal routing inside index.php  
- MVC-inspired organization (models, controllers, views)  
- PDO prepared statements and exceptions  
- Gemini API requests using cURL  

## 🎨 Frontend
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwindcss&logoColor=white)
![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)

- Tailwind via CDN  
- Custom design tokens for theme control  
- Vanilla JS for:
  - Tab switching  
  - Light/dark mode  
  - Admin live search  
  - CRUD modal logic  

## 🤖 AI Integration
![Gemini](https://img.shields.io/badge/Google_Gemini_API-4285F4?style=for-the-badge&logo=google&logoColor=white)

- Gemini v1beta  
- Model: **gemini-2.5-flash**  
- Auto-translation + explanation sentence  

## 🧰 Dev Tools
![VSCode](https://img.shields.io/badge/VS_Code-007ACC?style=for-the-badge&logo=visualstudiocode&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05033?style=for-the-badge&logo=git&logoColor=white)

- Git repo: `git@github.com:arshiasalehi/glossary.git`  
- Local PHP server  
- MySQL local instance  

---

# 🧠 Architecture Overview

## 🎨 Presentation Layer
- Tailwind-based code-editor styled UI  
- Three-tab navigation:
  - Glossary  
  - Dashboard  
  - Feedback  
- Dark/light theme with animated lamp  
- EVA animated loader for Gemini calls  

## ⚙️ Business Logic
- Glossary lookup → local DB first  
- Fallback to Gemini API → save & return  
- Validation & sanitization for inputs  
- Admin role verification  

## 🗄️ Data Access Layer
- MySQL with two main tables:
  - `terms` (word, translation, definition, created_at)  
  - `users` (seeded admin)  
- PDO prepared statements  
- CRUD models for terms and users  

---

