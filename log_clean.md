# Data Cleanup Log

Every cleanup SQL prepared for the live database (https://catest.ca). Newest entry first.
The SQL is run by hand in phpMyAdmin, never by Claude. Always run step 1 first and stop if the counts differ.

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
