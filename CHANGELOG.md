# Changelog

Snippet changes for https://catest.ca (Code Snippets plugin).
Status = active/inactive state of the snippet on the live site at the time of the entry.

## 2026-11-18 - Excel Quiz Importer: single quiz category + mock packs

- Snippet: Excel Quiz Importer (shortcode `[excel_quiz_importer]`)
- Local file: `snippets/excel-quiz-importer.php`
- Previous version: git commit `61f8680`
- Applied to the live site by: the user, manually
- Status: ACTIVE on the live site (updated by the user; confirmed working by a successful import on 2026-11-18 18:18)
- Changes:
  - No quiz categories are created any more. Every quiz goes into one quiz category whose ID is typed in the new form field "Quiz category ID" (e.g. "ABK", created by hand beforehand). The import stops if that ID does not exist.
  - Practice quizzes (one per test, split by columns B and C) are renamed to `<Quiz title prefix> - <Practice keyword> - <chapter title> NN`, e.g. `Alberta - Practice - 2. Traffic Control 01`.
  - New mock packs: all questions sorted by column D and cut into packs of N questions (new form field, default 30). The last pack keeps the remainder. Named `<Quiz title prefix> - <Mock title prefix> NN`, e.g. `Alberta - Learner's Tests (K) 01`. Mock packs reuse the same question IDs, so no question is inserted twice.
  - Quiz `ordering`: practice quizzes first, then mock packs.
  - New form fields: Quiz category ID, Practice keyword (default `Practice`), Mock title prefix (default `Learner's Tests (K)`), Questions per mock pack (default 30). Question code prefix default changed to `ABK`.
  - Text form fields are now unslashed (`wp_unslash`), so `Learner's` is not stored as `Learner\'s`.
- Expected result for `AB.xlsx`: 588 questions, 17 practice quizzes and 20 mock packs (19 x 30 + 18), all in one quiz category.
- Test import (2026-11-18, before 18:18): `AB.xlsx` imported by the user with Province code `AB`, Quiz category ID `73` ("ABK"), question code prefix `ABK`, quiz title prefix `Alberta`, practice keyword `Practice`, mock title prefix `Learner's Tests (K)`, 30 questions per mock pack. Result: success; the user checked the database and confirmed the data is correct.
- Done before the import: old quizzes, questions and answers were cleaned with the SQL in `log_clean.md` (entries 15:47, 15:55, 16:03), so the `ABK` prefix no longer clashes with old question codes.

## 2026-11-16 - Excel Quiz Importer: chapter support

- Snippet: Excel Quiz Importer (shortcode `[excel_quiz_importer]`)
- Local file: `snippets/excel-quiz-importer.php`
- Backup of the previous version: `backups/excel-quiz-importer.2026-11-16.before-chapters.php`
- Applied to the live site by: the user, manually
- Status: updated on the live site by the user (exact active/inactive state not recorded)
- Changes:
  - Reads two sheets: `<CODE>` (questions; new layout A-L with B = chapter, C = order) and `<CODE>i` (chapters: A = number, B = title).
  - Created one quiz category per chapter (`<CODE> | <chapter title>`) and one quiz per test; a test ends when column C restarts. Questions inside a test follow column C.
  - Removed the Pack Size field; added Province code field.
  - Later the same day: quiz categories are inserted with `author_id`.
