# The Las Pegasus Radio RESTful API

## Development roadmap:
- **(Scheduled)** Basic database connectivity
- Users (ID, unique nickname, e-mail address, password, permissions level, registration IP address, last login IP address, last login, avatar url, totp secret key, oauth2 code)
	- **(Scheduled)** Invite codes (ID, date issued, issuer, invite code (random sha1), boolean if already used, new user)
	- **(Scheduled)** User account registration\*2
	- **(Scheduled)** Getting user information\*1
	- **(Scheduled)** User credentials validation\*1
	- **(Scheduled)** User account edit function\*1
	- **(Scheduled)** User account deletion\*2
	- **(Scheduled)** Two factor authentication\*1
- Invite codes (ID, date issued, issuer, invite code (random sha1), boolean if already used, new user)
	- **(Scheduled)** Create new invite code\*2
	- **(Scheduled)** Validate invite code\*2
- Song entries (ID, song title, album title, song file location on drive, album cover url)
	- **(Scheduled)** Adding and removing song entries\*1
	- **(Scheduled)** Viewing song information (Both from database and Icecast)
- Song likes (ID, song ID, user ID, status)
	- **(Scheduled)** Adding status for user\*1
	- **(Scheduled)** Getting status information for user\*1
	- **(Scheduled)** Removing statuses\*1

\*1 - Function **not yet** available for regular API users.
\*2 - Function **never** available for regular API users.

Development roadmap may be subject to be changed.

