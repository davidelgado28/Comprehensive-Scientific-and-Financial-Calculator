# Scientific and Financial Calculator

A complete, monolithic web application built with **Pure PHP** and styled with **Tailwind CSS**, designed to run seamlessly on modern serverless platforms like **Vercel** using a PHP runtime. 

No external frameworks, databases, or third-party APIs are required — everything runs efficiently on backend PHP logic with frontend state persistence.

---

## Features Included

### 1. Compound Interest Simulator
* **Inputs:** Initial Principal, Interest Rate (monthly or annual), Time Period (months or years), and Optional Recurring Monthly Contributions.
* **Backend Logic:** Iterative calculation tracking month-by-month evolution, separating total invested capital from total interest earned.
* **Outputs:** Summary cards displaying Total Invested, Total Interest, and Final Balance, followed by a detailed monthly breakdown table (Month, Initial Balance, Contribution, Monthly Interest, Final Balance).

### 2. Numerical Base Converter
* **Inputs:** Input string number, Source Base dropdown, and Target Base dropdown.
* **Supported Bases:** Decimal (10), Binary (2), Octal (8), and Hexadecimal (16).
* **Backend Logic:** Strict regex input validation checking whether characters match the selected source base, followed by secure conversions using native PHP functions (`base_convert`).

### 3. Quadratic Equation Solver (Bhaskara's Formula)
* **Inputs:** Coefficients $a$, $b$, and $c$ ($ax^2 + bx + c = 0$).
* **Backend Logic:** 
  * Validates that $a \neq 0$.
  * Computes Delta ($\Delta = b^2 - 4ac$).
  * Handles three scenarios: Negative Delta (no real roots), Zero Delta (two equal real roots), and Positive Delta (two distinct real roots).
* **Outputs:** Delta value, descriptive explanations, and calculated roots ($X_1$ and $X_2$).

---

## 📂 Project Structure

```text
calculator/
├── api/
│   └── index.php      # Single-file application handling backend logic & UI
└── vercel.json        # Vercel deployment configuration
