# reports-from-dash
Email Reports from Analytics Dashboard

# Usage
If a client wants to enable reports, he needs to undertake two steps:
1. Let us know for what domain(s) he wants reports and to which email adress reports should be sent
2. Create a view for the report. This view must be named `dailyReport` (for daily reports) or `weeklyReport` (for weekly reports). There can only be one such view per domain at a time.

The information from step 1 goes into `config.ini`, with one line for each report, domain and email adress separated by a comma (see example in `config.ini`).

`main.php` should be scheduled to execute once per day, e.g. using a cron job.

# Limitations
* The tool only supports one reporting view and one recipient
* The reporting view must not be a pivotal view
* Reports are in CSV format
* Emailing is using PHP's `mail()` function, so `/usr/bin/sendmail` should exist and be configured for sending emails
