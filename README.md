# 42-Matcha-WEB
## Dating Web Site

In this project I used PHP Slim Framework for BackEnd.
PHP Slim - is a lightweight OOP PHP framework. And it's recommended to use it in this project.

Matcha is a real challenging project because of user real time interactions and real time notifications.
I've done this project with my awesome teammate @oivasenk. 

### Registration and Signing-in
The app must allow a user to register asking at least an email address, a username, a last name, a first name and a password that is somehow protected. After the registration, an e-mail with an unique link must be sent to the registered user to verify his account.

The user must then be able to connect with his/her username and password. He/She must be able to receive an email allowing him/her to re-initialize his/her password should the first one be forgotten and disconnect with 1 click from any pages on the site.

### User profile
• Once connected, a user must fill his or her profile, adding the following information: <br>
  ◦ The gender. <br>
  ◦ Sexual preferences. <br>
  ◦ A biography. <br>
  ◦ A list of interests with tags (ex: #vegan, #geek, #piercing etc...). These tags must be reusable. <br>
  ◦ Pictures, max 5, including 1 as profile picture. <br>
• At any time, the user must be able to modify these information, as well as the last name, first name and email address. <br>
• The user must be able to check who looked at his/her profile as well as who “liked” him/her. <br>
• The user must have a public “fame rating” 1. <br>
• The user must be located using GPS positionning, up to his/her neighborhood. If the user does not want to be positionned, you must find a way to locate him/her even without his/her knowledge.2 The user must be able to modify his/her GPS position in his/her profile. <br>

### Browsing
The user must be able to easily get a list of suggestions that match his/her profile. <br>
• You will only propose “interesting” profiles for example, only men for a heterosexual girls. You must manage bisexuality. If the orientation isn’t specified, the user will be considered bi-sexual. <br>
• You must cleverly match3 profiles: <br>
  ◦ Same geographic area as the user. <br>
  ◦ With a maximum of common tags. <br>
  ◦ With a maximum “fame rating”. <br>
• You must show in priority people from the same geographical area. <br>
• The list must be sortable by age, location, “fame rating” and common tags. <br>
• The list must be filterable by age, location, “fame rating” and common tags. <br>
