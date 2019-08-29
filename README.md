# The Las Pegasus Radio RESTful API

## Development roadmap:
- **(Done)** ~~Basic database connectivity~~
- Users (ID, unique nickname, e-mail address, password, permissions level, registration IP address, last login IP address, last login, avatar url, totp secret key, oauth2 code)
  - **(Done, no e-mail verification yet)** ~~User account registration~~
  - **(Done, not available through API yet)** ~~User credentials validation~~
  - **(Done)** ~~Getting user information~~
  - **(Done)** ~~User account edit function~~
  - **(Done)** ~~User account deletion~~
  - **(Done, not available through API yet)** ~~Two factor authentication~~
- Invite codes (ID, date issued, issuer, invite code (random sha1), boolean if already used, new user)
	- **(Scheduled)** Create new invite code
	- **(Scheduled)** Validate invite code
- Song entries (ID, song title, album title, song file location on drive, album cover url)
	- **(Scheduled)** Adding and removing song entries
	- **(Scheduled)** Viewing song information (Both from database and Icecast)
- Song likes (ID, song ID, user ID, status)
	- **(Scheduled)** Adding status for user
	- **(Scheduled)** Getting status information for user
	- **(Scheduled)** Removing statuses

Development roadmap may be subject to be changed.

