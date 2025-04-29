# Php login/registration system

Here we have an example of a PHP login and registration system using MySQL for storage, functionality to allow logged-in users to add notes to a MySQL database. This includes basic functionality with security considerations like password hashing and prepared statements to prevent SQL injection, CSRF protection (CSRF regeneration and timeout).

-----



![My Image](images/dashboard.jpg)




Key Security Features:

CSRF Protection: Tokens ensure form submissions come from the user’s session.
Token Timeout: Tokens expire after 30 minutes.
Token Regeneration: New tokens after actions prevent reuse.
Prepared Statements: Prevent SQL injection.
Password Hashing: Secures passwords with password_hash().
XSS Prevention: htmlspecialchars() escapes output.
Session Authentication: Restricts access to logged-in users.