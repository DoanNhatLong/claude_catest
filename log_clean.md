# Data Cleanup Log

Every cleanup SQL prepared for the live database (https://catest.ca). Newest entry first.
The SQL is run by hand in phpMyAdmin, never by Claude. Always run step 1 first and stop if the counts differ.

---

## 2026-11-18 15:55 (+07:00) - Delete every question except "SIGN..." codes

- **Purpose:** Clean `snnj_aysquiz_questions` so only the sign questions (`question_title` = `SIGN###`) remain.
- **Source backup:** `snnj_aysquiz_questions_backup.sql` (3.8 MB, modified 2026-11-18 15:53), scanned by a local script (columns `id`, `question_title`, `category_id` only).
- **Scan result:** 8087 questions. Titles starting with "SIGN": 223, all shaped `SIGN###`, all in question category 10. No other title contains "sign".
- **Will be deleted (7864), by code prefix:**
  - `ABK` 588, `ABKT` 588, `BCK` 573, `MAK` 573, `NBK` 547, `NLK` 581, `NSK` 566, `NTK` 577, `NUK` 550, `ONK` 563, `PEK` 518, `QCK` 549, `SKK` 520, `YTK` 569, `YTC` 2
  - This includes every `ABK....` / `ABKT....` question that exists in the backup, i.e. any province data already imported with the new layout.
- **Condition:** `question_title NOT LIKE 'SIGN%'`
- **Expected:** 7864 deleted, 223 kept.
- **Not cleaned by this SQL:** answers of the deleted questions stay in `snnj_aysquiz_answers` (orphan rows); quizzes in `snnj_aysquiz_quizes` that still list deleted IDs in `question_ids`.
- **Status:** prepared, not yet run.

Step 1 - check (must return 7864 and 223; stop if different):

```sql
SELECT COUNT(*) FROM snnj_aysquiz_questions WHERE question_title NOT LIKE 'SIGN%';
SELECT COUNT(*) FROM snnj_aysquiz_questions WHERE question_title LIKE 'SIGN%';
```

Step 2 - delete:

```sql
DELETE FROM snnj_aysquiz_questions WHERE question_title NOT LIKE 'SIGN%';
```

---

## 2026-11-18 15:47 (+07:00) - Delete every quiz except "Traffic Signs" (re-run)

- **Purpose:** Clean `snnj_aysquiz_quizes` so only the "Traffic Signs" quizzes remain, before importing the new single-category data (e.g. ABK).
- **Source backup:** `snnj_aysquiz_quizes_backup.sql` (9.96 MB, modified 2026-11-18 15:29), scanned by a local script (columns `id`, `title`, `quiz_category_id` only).
- **Scan result:** 895 quizzes. Title variants matching "Traffic Sign": only `Traffic Signs -Test NN` (11 quizzes, all in quiz category 17).
- **Will be deleted (884):**
  - quiz categories 24-36 and 38-50: `<Province> Knowledge - Pack NN` (865 quizzes)
  - quiz category 37: `TEST - Pack 01/02` (2 quizzes)
  - quiz categories 64-72: `Alberta - <chapter> - Test N` from the 2026-11-16 import (17 quizzes)
- **Condition:** `title NOT LIKE 'Traffic Signs -Test %'`
- **Expected:** 884 deleted, 11 kept.
- **Run before the new import:** new quizzes do not match "Traffic Signs" and would be deleted too.
- **Not cleaned by this SQL:** questions (`snnj_aysquiz_questions`), answers (`snnj_aysquiz_answers`), quiz categories (`snnj_aysquiz_quizcategories`), hidden posts linked by `custom_post_id` (`snnj_posts`).
- **Status:** prepared, not yet run.

Step 1 - check (must return 884 and 11; stop if different):

```sql
SELECT COUNT(*) FROM snnj_aysquiz_quizes WHERE title NOT LIKE 'Traffic Signs -Test %';
SELECT COUNT(*) FROM snnj_aysquiz_quizes WHERE title LIKE 'Traffic Signs -Test %';
```

Step 2 - delete:

```sql
DELETE FROM snnj_aysquiz_quizes WHERE title NOT LIKE 'Traffic Signs -Test %';
```

---

## 2026-11-18 15:39 (+07:00) - Delete every quiz except "Traffic Signs"

- **Purpose:** Remove all old quizzes (province "Knowledge - Pack" quizzes, "TEST - Pack" quizzes and the 2026-11-16 chapter test quizzes) before importing the new single-category data (e.g. ABK). Only the 11 "Traffic Signs -Test NN" quizzes are kept.
- **Source backup:** `snnj_aysquiz_quizes_backup.sql` (table `snnj_aysquiz_quizes`, 895 quizzes)
- **Condition:** `title NOT LIKE 'Traffic Signs -Test %'`
- **Expected:** 884 quizzes deleted (quiz category IDs 24-50 and 64-72), 11 kept (all in quiz category 17).
- **Run before the new import:** the new quizzes do not match "Traffic Signs" and would be deleted too.
- **Not cleaned by this SQL:** questions (`snnj_aysquiz_questions`), answers (`snnj_aysquiz_answers`), quiz categories (`snnj_aysquiz_quizcategories`), and the hidden posts linked by `custom_post_id` (`snnj_posts`).
- **Status:** prepared, not yet run.

Step 1 - check (must return 884 and 11):

```sql
SELECT COUNT(*) FROM snnj_aysquiz_quizes WHERE title NOT LIKE 'Traffic Signs -Test %';
SELECT COUNT(*) FROM snnj_aysquiz_quizes WHERE title LIKE 'Traffic Signs -Test %';
```

Step 2 - delete:

```sql
DELETE FROM snnj_aysquiz_quizes WHERE title NOT LIKE 'Traffic Signs -Test %';
```
