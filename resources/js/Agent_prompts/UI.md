# Bean Origin UI Design System

## Design Direction

Bean Origin should have the visual character of a modern developer
console inspired by Claude Code:

- minimal
- dark
- compact
- technical
- information-dense
- calm
- highly readable
- low visual noise
- strong hierarchy
- subtle borders
- restrained colors

Do NOT copy Claude branding, logos or proprietary UI.
Use the design principles only.

---

# 1. GLOBAL LAYOUT

## Application

Background:
#0D0F10

Primary surface:
#121516

Secondary surface:
#171A1B

Elevated surface:
#1C2021

Border:
#292D2E

Primary text:
#E7E9E9

Secondary text:
#A1A6A7

Muted text:
#6F7677

Maximum content width:
1440px

Application padding:
24px

Desktop page padding:
32px

Tablet page padding:
24px

Mobile page padding:
16px

---

# 2. SPACING SYSTEM

Use a 4px base spacing system.

4px   = xs
8px   = sm
12px  = md
16px  = lg
20px  = xl
24px  = 2xl
32px  = 3xl
40px  = 4xl
48px  = 5xl
64px  = 6xl

Prefer these values.

Do not randomly use values such as:
13px
17px
19px
27px
31px

unless required by an existing component.

---

# 3. PAGE STRUCTURE

Page header:

margin-bottom: 24px

Page title:
32px

Page subtitle:
14px

Gap between title and subtitle:
6px

Gap between page header and content:
24px

Section spacing:
32px

Major section spacing:
48px

Never allow unrelated sections to visually touch.

---

# 4. TYPOGRAPHY

Primary UI font:

Inter, system-ui, sans-serif

Technical/data font:

JetBrains Mono, monospace

## Page title

font-size: 28px
line-height: 36px
font-weight: 600
letter-spacing: -0.02em

## Section title

font-size: 18px
line-height: 24px
font-weight: 600

## Card title

font-size: 15px
line-height: 20px
font-weight: 600

## Body

font-size: 14px
line-height: 20px
font-weight: 400

## Secondary text

font-size: 13px
line-height: 18px

## Caption

font-size: 12px
line-height: 16px

## Small technical text

font-size: 11px
line-height: 16px

Use monospace for:

- batch IDs
- lot IDs
- transaction IDs
- blockchain hashes
- timestamps
- quantities
- commodity codes
- system logs
- technical statuses

---

# 5. NAVIGATION

Sidebar width:

240px

Collapsed sidebar:

64px

Sidebar padding:

12px

Navigation item height:

36px

Navigation item horizontal padding:

10px

Navigation item gap:

8px

Navigation group spacing:

24px

Navigation group label:

11px
font-weight: 600
letter-spacing: 0.08em
text-transform: uppercase

Icon size:

16px

Navigation text:

13px

Active navigation item:

background: #1C2021

border-left: 2px

Do not use oversized navigation items.

---

# 6. TOP BAR

Height:

56px

Horizontal padding:

16px 24px

Bottom border:

1px solid #292D2E

Search / command input:

height: 36px

Border radius:

6px

Font size:

13px

---

# 7. CARDS / PANELS

Avoid large decorative cards.

Panel padding:

16px

Dense panel padding:

12px

Large panel padding:

24px

Border:

1px solid #292D2E

Border radius:

6px

Do not use:

border-radius: 16px
border-radius: 20px
border-radius: 24px

unless explicitly required.

Avoid heavy shadows.

---

# 8. GRID

Dashboard metric grid:

4 columns desktop

2 columns tablet

1 column mobile

Gap:

12px

Use 12px gaps for dense operational interfaces.

Use 16px gaps for normal content.

Use 24px gaps between major layout regions.

---

# 9. METRIC BLOCKS

Metric label:

11px
uppercase
letter-spacing: 0.06em

Metric value:

24px
line-height: 32px
font-weight: 600

Metric secondary information:

12px

Metric internal spacing:

8px

Do not make dashboard numbers excessively large.

---

# 10. TABLES

Table header:

12px

Table body:

13px

Technical columns:

12px monospace

Table row minimum height:

44px

Dense row:

36px

Cell horizontal padding:

12px

Cell vertical padding:

10px

Header height:

40px

Borders:

1px solid #292D2E

Avoid excessive rounded table containers.

---

# 11. BUTTONS

Primary button:

height: 36px
padding: 0 14px
font-size: 13px
font-weight: 500
border-radius: 6px

Secondary button:

height: 36px
padding: 0 14px

Small button:

height: 30px
padding: 0 10px
font-size: 12px

Icon button:

32px × 32px

Do not use oversized buttons.

---

# 12. INPUTS

Height:

36px

Small input:

32px

Large input:

40px

Horizontal padding:

10px

Font:

13px

Border:

1px solid #292D2E

Border radius:

6px

Label:

12px

Label margin-bottom:

6px

Form field spacing:

16px

---

# 13. COMMAND PALETTE

Keyboard shortcut:

⌘K

Command palette width:

640px

Maximum width:

calc(100vw - 32px)

Input height:

48px

Command result height:

40px

Result padding:

8px 12px

Section spacing:

12px

Use monospace for commands and technical identifiers.

---

# 14. ACTIVITY / SYSTEM LOG

Activity row:

min-height: 32px

Timestamp:

11px monospace

Event:

13px

Technical metadata:

12px monospace

Horizontal gap:

12px

Vertical gap:

4px

Use subtle status indicators.

Example:

12:41:08  BATCH_CREATED
          UG-RBT-2026-0041

12:43:02  QUALITY_RECORDED
          Moisture: 11.8%

12:45:03  LOT_CREATED
          LOT-2026-0081

---

# 15. TRACEABILITY TIMELINE

Timeline node:

8px

Timeline line:

1px

Event title:

13px

Event metadata:

12px

Event spacing:

16px

Major workflow stages:

32px apart

Use the timeline to communicate:

Farm
↓
Collection
↓
Batch
↓
Processing
↓
Lot
↓
Inventory
↓
Blockchain
↓
Market

---

# 16. STATUS SYSTEM

Success:
#7EE787

Warning:
#D29922

Error:
#F85149

Info:
#58A6FF

Do not use status colors as large backgrounds.

Use them primarily for:

- dots
- icons
- borders
- badges
- indicators
- small highlights

---

# 17. BADGES

Height:

22px

Horizontal padding:

7px

Font:

11px

Border radius:

4px

Font weight:

500

Keep badges compact.

---

# 18. MODALS / DRAWERS

Modal width:

480px

Large modal:

720px

Maximum:

90vw

Padding:

24px

Header:

20px

Header bottom spacing:

16px

Footer:

16px

Drawer width:

420px

Do not create full-screen dialogs unless necessary.

---

# 19. RESPONSIVE DESIGN

Desktop:

≥1200px

Tablet:

768px–1199px

Mobile:

<768px

At mobile:

Page padding:
16px

Sidebar:
collapsed / drawer

Grid:
1 column

Table:
horizontal scroll or responsive transformation

Buttons:
avoid unnecessary full-width buttons

---

# 20. VISUAL DENSITY

Bean Origin is an operational platform.

Prefer:

compact layouts
small gaps
clear separators
dense tables
structured information
visible system state

Avoid:

huge cards
large empty spaces
oversized typography
excessive gradients
glassmorphism
large shadows
decorative illustrations
unnecessary animations

The interface should feel like professional infrastructure software.

---

# 21. ANIMATION

Keep animations subtle.

Fast interaction:

120ms

Normal transition:

180ms

Complex transition:

240ms

Do not animate everything.

Avoid:

bounce
elastic
large scaling
dramatic page transitions

Prefer:

opacity
background transition
border transition
small translate

---

# 22. ACCESSIBILITY

Minimum interactive target:

32px desktop
40px mobile where practical

Keyboard navigation must work.

Visible focus state required.

Never rely only on color to communicate status.

Maintain readable contrast.

---

# 23. UX IMPLEMENTATION RULE

Before modifying a page:

1. Inspect existing implementation.
2. Identify the user's primary task.
3. Identify the information hierarchy.
4. Identify existing reusable components.
5. Apply these design tokens.
6. Reuse components.
7. Implement.
8. Review spacing and typography.
9. Check desktop.
10. Check mobile.

Never create arbitrary spacing or typography values when an existing
design token can be used.

---

# 24. FINAL DESIGN TEST

Before considering a page complete, verify:

[ ] Page title = 28px
[ ] Section titles = 18px
[ ] Body = 14px
[ ] Secondary text = 13px
[ ] Technical text = 11–12px
[ ] Page padding = 32px desktop / 16px mobile
[ ] Section spacing = 32px
[ ] Major section spacing = 48px
[ ] Panel padding = 16px
[ ] Grid gap = 12–16px
[ ] Borders = 1px
[ ] Radius = 6px
[ ] Buttons = 36px
[ ] Inputs = 36px
[ ] Navigation items = 36px
[ ] No unnecessary shadows
[ ] No oversized cards
[ ] No arbitrary spacing values
[ ] Mobile layout checked
[ ] Loading/empty/error states implemented
