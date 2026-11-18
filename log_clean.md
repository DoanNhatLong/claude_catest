# Data Cleanup Log

Every cleanup SQL prepared for the live database (https://catest.ca). Newest entry first.
The SQL is run by hand in phpMyAdmin, never by Claude. Always run step 1 first and stop if the counts differ.

---

## 2026-11-18 16:03 (+07:00) - Delete every answer except those of "SIGN..." questions

- **Purpose:** Clean `snnj_aysquiz_answers` so only the answers of the 223 sign questions (`SIGN###`) remain. Replaces the 15:59 entry below.
- **Source backups:** `snnj_aysquiz_answers_backup.sql` (2.5 MB, modified 2026-11-18 16:01; columns `id`, `question_id` only) and `snnj_aysquiz_questions_backup.sql` (columns `id`, `question_title` only), scanned by a local script.
- **Scan result:** 32352 answers. 892 belong to SIGN questions (exactly 4 per question, all 223 SIGN questions have answers).
- **Will be deleted (31460):**
  - 31456 answers of non-SIGN questions (4 per question x 7864): `ABK` 2352, `ABKT` 2352, `BCK` 2292, `MAK` 2292, `NBK` 2188, `NLK` 2324, `NSK` 2264, `NTK` 2308, `NUK` 2200, `ONK` 2252, `PEK` 2072, `QCK` 2196, `SKK` 2080, `YTK` 2276, `YTC` 8
  - 4 answers that were already orphan in the backup (their question does not exist).
- **Condition:** the answer's question does not exist OR its `question_title` does not start with `SIGN`. Correct whether the question cleanup (15:55) has been run or not.
- **Expected:** 31460 deleted, 892 kept (if the questions of the 15:55 entry are already deleted, the delete count is the same: they become orphans).
- **Status:** RUN by the user in phpMyAdmin (reported done 2026-11-18 16:06; result counts not reported).

Step 1 - check (must return 31460 and 892; stop if different):

```sql
SELECT COUNT(*) FROM snnj_aysquiz_answers a
LEFT JOIN snnj_aysquiz_questions q ON q.id = a.question_id
WHERE q.id IS NULL OR q.question_title NOT LIKE 'SIGN%';

SELECT COUNT(*) FROM snnj_aysquiz_answers a
JOIN snnj_aysquiz_questions q ON q.id = a.question_id
WHERE q.question_title LIKE 'SIGN%';
```

Step 2 - delete:

```sql
DELETE a FROM snnj_aysquiz_answers a
LEFT JOIN snnj_aysquiz_questions q ON q.id = a.question_id
WHERE q.id IS NULL OR q.question_title NOT LIKE 'SIGN%';
```

---

## 2026-11-18 15:59 (+07:00) - Delete orphan answers (answers whose question no longer exists)

- **Superseded by the 16:03 entry above** (same goal, with exact counts and a condition that also works before the question cleanup). Use 16:03 instead.

- **Purpose:** Remove rows in `snnj_aysquiz_answers` left behind after questions are deleted (e.g. after the 15:55 entry below).
- **Source backup:** none needed. The condition is checked directly against the live tables.
- **Condition:** answer's `question_id` does not exist in `snnj_aysquiz_questions`.
- **When to run:** AFTER the question cleanup. Safe to re-run any time later; it only removes answers with no question.
- **Expected:** a positive count. If the 7864 questions of the 15:55 entry are deleted and each had 4 answers, roughly 31,000 rows.
- **Status:** NOT run (superseded by 16:03).

Step 1 - check (count of orphan answers):

```sql
SELECT COUNT(*) FROM snnj_aysquiz_answers a
LEFT JOIN snnj_aysquiz_questions q ON q.id = a.question_id
WHERE q.id IS NULL;
```

Step 2 - delete:

```sql
DELETE a FROM snnj_aysquiz_answers a
LEFT JOIN snnj_aysquiz_questions q ON q.id = a.question_id
WHERE q.id IS NULL;
```

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
- **Status:** RUN by the user in phpMyAdmin (reported done 2026-11-18 16:06; result counts not reported).

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
- **Status:** RUN by the user in phpMyAdmin (reported done 2026-11-18 16:06; result counts not reported).

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
- **Status:** NOT run separately (same SQL was run via the 15:47 entry).

Step 1 - check (must return 884 and 11):

```sql
SELECT COUNT(*) FROM snnj_aysquiz_quizes WHERE title NOT LIKE 'Traffic Signs -Test %';
SELECT COUNT(*) FROM snnj_aysquiz_quizes WHERE title LIKE 'Traffic Signs -Test %';
```

Step 2 - delete:

```sql
DELETE FROM snnj_aysquiz_quizes WHERE title NOT LIKE 'Traffic Signs -Test %';
```
