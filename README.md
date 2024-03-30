
# Laravel custom auth guard
Laravel is shipped with ready to use auth system and it works perfectly fine for most cases, but trying to customize it is a really hard process for me; that's why i decided to create my own custom auth guard.

## Features
this guard solves some problems just to name a few:

- Instead of storing auth token in users table, it stores tokens in separate table. this way it's easy to store multiple auth tokens for a single user and allow them to login from many devices.
- User has the ability to list all devices and logout from specific device if needed.
- Match againist multiple columns in users table along with password column. for example, user can enter email or phone with password and it will work.
- It's easy to customize for other features in the future if needed. for example it will be easy to know if user inactive and display proper message.


## Notes

- Postman collection could be found at "auth_test_collection.postman_collection.json" file in case you want to make some testing.

- Tested on laravel 10.0
