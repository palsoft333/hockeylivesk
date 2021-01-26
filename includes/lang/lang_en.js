if($("#calendar").length != 0) {
  $("#calendar").calendario({
      weeks : ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
      weekabbrs : ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
      months : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
      monthabbrs : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      displayWeekAbbr : false,
      displayMonthAbbr : false,
      startIn : 1,
      fillEmpty: true,
      zone: '00:00',
    events : ['click', 'focus', 'hover'],
      checkUpdate: false,
      weekdays: 'MON, TUE, WED, THU, FRI',
      weekends: 'SAT, SUN'
  });
}

var LANG_ERROR = "Error";
var LANG_SETTINGS = "Settings";
var LANG_MAIL = "email";

var LANG_CALENDAR_GAMESFOR = "Games for";
var LANG_FLASH_CANNOT = "Cannot load quick news.";

var LANG_USERPROFILE_PASSDIDNTMATCH = "Passwords do not match!";
var LANG_USERPROFILE_PASSATLEAST6 = "Password must be at least 6 characters long!";
var LANG_USERPROFILE_PASSCHANGETITLE = "Password change";
var LANG_USERPROFILE_PASSCHANGEOK = "Password has been changed successfully.";
var LANG_USERPROFILE_PASSCHANGEERROR = "An error occurred while changing the password. Did you enter the correct current password?";
var LANG_USERPROFILE_SETTINGSCHANGED = "Settings changed successfully.";
var LANG_USERPROFILE_SETTINGSERROR = "An error occurred while changing the settings.";
var LANG_USERPROFILE_MIKECHECK = "USA scores 2:0";
var LANG_USERPROFILE_VOICE = "UK English Female";

var LANG_GAMES_BETTING = "Betting";
var LANG_GAMES_BET = "Bet";
var LANG_GAMES_BETADDED = "was added";
var LANG_GAMES_BETREMOVED = "The bet has been deleted";

var LANG_FANTASY_F = "Forward";
var LANG_FANTASY_D = "Defense";
var LANG_FANTASY_GK = "Goalies team";
var LANG_FANTASY_ROUND = "round";
var LANG_FANTASY_ADDED = "added";
var LANG_FANTASY_PICKJUSTONE = "Choose only one player at a time!";
var LANG_FANTASY_DRAFT = "Draft";
var LANG_FANTASY_CONFIRMTEAM = "Confirm your team.";
var LANG_FANTASY_PICKANOTHER = "Choose another player.";
var LANG_FANTASY_CONFIRMBUTTON = "Confirm my selection";
var LANG_FANTASY_ERRORSAVING = "There was an error saving your team.";

var LANG_COMMENTS_CAPTCHAERROR = "Your registration did not pass the spam test. Please try again or send us an";
var LANG_COMMENTS_ADDED = "Comment added successfully.";
var LANG_COMMENTS_REMOVED = "Comment deleted successfully.";

var LANG_REGISTER_USERATLEAST3 = "Username must be at least 3 characters long!";
var LANG_REGISTER_ALREADY = "This email has already been registered with us.";
var LANG_REGISTER_DIDYOUFORGETPASS = "Forgot your password?";
var LANG_REGISTER_INCORRECTFIELDS = "Incorrectly filled registration fields!";
var LANG_REGISTER_USERTAKEN = "The username you entered already exists!";

var LANG_LOGIN_WRONGEMAIL = "Email is in the wrong format";
var LANG_LOGIN_EMAILSENT = "Email has been sent";
var LANG_LOGIN_EMAILDOESNTEXIST = "This email does not exist in our database";