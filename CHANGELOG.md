# Release Notes for Flarum SSO for Craft 4

# 1.0.4
- Updates login function to set token cookie as long lived (one year expiry)
- Updates setCookie function in api client to default to 24h when not long lived (increased from 1h)

# 1.0.3
- CraftCMS 5.x Support

## 1.0.2
- Fixed error when logging in and recieving an error from Flarum API (Error is now caught and logged.)
- Ensured that the users password is kept in sync between Craft CMS and Flarum (this was the major cause of the above errors.)

## 1.0.1
- Fixed internal server error when creating user from Craft admin panel.

## 1.0.0
- Initial release
