ONLINE VOTING SYSTEM - LOCAL XAMPP INSTALLATION

Requirements:
- XAMPP with Apache, PHP and MySQL/MariaDB
- A modern browser
- Internet access for Tailwind CSS CDN styling (the PHP application itself runs locally)

INSTALLATION:
1. Extract the online_voting_system folder into:
   C:\xampp\htdocs\online_voting_system
2. Open XAMPP Control Panel and start Apache and MySQL.
3. Open http://localhost/phpmyadmin
4. Choose Import, select database.sql from this project, then click Import.
5. Check config.php. Default XAMPP MySQL settings are host 127.0.0.1, user root, blank password.
   If you configured a MySQL password, update $pass in config.php.
6. Visit http://localhost/online_voting_system/setup.php
7. Create the first administrator account with your own details and a strong password.
8. Sign in at http://localhost/online_voting_system/

HOW TO USE:
1. Sign in as administrator.
2. Create an election with start and end times.
3. Add at least two candidates while the election is in Draft status.
4. Publish the election.
5. Voters register from the login page and sign in to cast one vote.
6. Close the election from the admin dashboard. Voters can view results after closure.
7. Administrators can view results at any time.

IMPORTANT SECURITY NOTES:
- This is a learning/MVP project, not a certified platform for binding public elections.
- Do not expose your local XAMPP server directly to the public internet.
- For real elections, add verified voter identity, independent security review, audit logging, stronger operational controls, backups, HTTPS and jurisdiction-specific compliance.
- Tailwind is loaded from its CDN, so styling requires internet access. For fully offline use, install and compile Tailwind locally.
- Delete or rename setup.php after creating the first administrator. It disables itself once an admin exists.
- Keep PHP and XAMPP updated. Use a strong MySQL password outside a local development environment.
