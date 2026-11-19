# Quiz Import - Business Rules

Read this before changing the Excel importer or the quiz data on https://catest.ca.
Code: `snippets/excel-quiz-importer.php` (Code Snippets plugin, shortcode `[excel_quiz_importer]`, admin only).
History: `CHANGELOG.md` (importer versions), `log_clean.md` (data cleanups).

## Quiz Maker data model (plugin is fixed, schema must not change)

| Table | Meaning | Key columns |
|---|---|---|
| `snnj_aysquiz_quizcategories` | Quiz category ("folder"), e.g. `ABK` | `id`, `author_id`, `title`, `description`, `published` |
| `snnj_aysquiz_quizes` | One quiz / test | `title`, `quiz_category_id`, `question_ids` (ordered comma list), `ordering`, `options` (JSON) |
| `snnj_aysquiz_questions` | Question | `question_title` (code, e.g. `ABK0001`), `category_id` (QUESTION category, Excel column L), `question`, `explanation` |
| `snnj_aysquiz_answers` | 4 answers per question | `question_id`, `answer`, `correct`, `ordering` |
| `snnj_aysquiz_categories` | QUESTION categories (different from quiz categories) | IDs 3-10 used by column L; 10 = signs |

- Question order inside a quiz = order of IDs in `question_ids` (needs `randomize_questions`, `enable_questions_ordering_by_cat`, `enable_question_bank` = "off" in `options`; the importer sets them).
- No custom tables, no extra columns. Chapters exist only in quiz titles.

## Excel input (one file per province, e.g. `AB.xlsx`)

- Sheet `<CODE>` (e.g. `AB`), one row per question:
  A = row no. (ignored), B = chapter no., C = order inside a practice test, D = question no. (code + mock order), E = question, F-I = answers A-D, J = explanation, K = correct letter, L = question category ID.
- Sheet `<CODE>i` (e.g. `ABi`): A = chapter no., B = chapter title (e.g. `2. Traffic Control`). Only these two columns. Chapter numbers may start at 0 and may have gaps (MA uses 0; BC uses 1,3,4,5,6,8).
- Column K must be A, B, C or D; other rows are skipped and listed (e.g. MA rows 233, 243, 264 had J and K swapped in `K.xlsx`).
- Practice test size differs per province (AB about 30, BC about 50); it always follows column C.
- Source workbook with all provinces: `K.xlsx` (sheets `AB`/`ABi`, `BC`/`BCi`, ... `YT`/`YTi`). Split one province into its own file before import (as done for `AB.xlsx`; keep only the two sheets, drop empty rows and extra columns).
- Excel files are git-ignored.

## What one import creates (all in ONE quiz category, ID typed in the form)

1. Questions + answers, inserted once. Code = `<prefix>` + column D padded to 4 digits (`ABK0001`).
2. Practice quizzes: inside each chapter (B), a new test starts when column C goes back (1..30, then 1). Questions ordered by C.
   Title: `<Quiz title prefix> - <Practice keyword> - <chapter title from CODEi> NN`, e.g. `Alberta - Practice - 2. Traffic Control 01`. Chapter titles come from the sheet, never hard-coded.
3. Mock packs: all questions sorted by column D, cut into packs of N (form field, default 30); the last pack keeps the remainder.
   Title: `<Quiz title prefix> - <Mock title prefix> NN`, e.g. `Alberta - Learner's Tests (K) 01`. Same question IDs as the practice quizzes.
4. `ordering`: practice quizzes first (1..), then mock packs.
- No quiz categories are created; the category (e.g. `ABK`) is made by hand in Quiz Maker > Quiz Categories first.
- All-or-nothing transaction; import refused if any question code already exists.

## Form values used for Alberta (2026-11-18, success)

`AB.xlsx` / Province code `AB` / Quiz category ID `73` (ABK) / default question category `26` (unused when L is filled) / prefix `ABK` / `Alberta` / `Practice` / `Learner's Tests (K)` / `30`.
Result: 588 questions, 2352 answers, 17 practice quizzes, 20 mock packs (19 x 30 + 18).

Other provinces follow the same pattern: code `XXK`, title prefix = province name, category `XXK` created by hand.

## Admin view

- Quiz Maker > Quizzes: filter by category (e.g. ABK) shows all quizzes of the province; search `Practice` or `Learner's Tests` to split the two kinds.

## State after the 2026-11-18 cleanup (see `log_clean.md`)

- Kept: 11 quizzes `Traffic Signs -Test NN` (quiz category 17), 223 questions `SIGN###` (question category 10) and their 892 answers.
- Everything else (old province packs and questions) was deleted, then Alberta was imported fresh.

## Open items

- Empty old quiz categories still exist: `ABK | 1..9` (IDs 64-72), old province categories (24-50, incl. 38 `ABK`).
- Hidden posts of deleted quizzes may remain in `snnj_posts` (linked via `custom_post_id`).
- Other provinces not imported yet.
- Frontend display "province -> chapter -> test" is not built; chapter info is only in quiz titles.
