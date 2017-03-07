/*Safari Driver requires a setup at browser level to support Selenium Webdriver Test automation
 * **************************************************************************************************
 * Please follow the below steps to install Mac Safari Selenium WebDriver before running scripts
 * **************************************************************************************************
1. Generate a signed Safari Developer certificate for the WebDriver extension by signing up for "Safari
Developer Program" (which is free). Register in website https://developer.apple.com/programs/safari/, and
follow the on screen instructions to generate the signed certificate. Once the certificate is ready,
download it to the local machine.

2. Create some folder (say "selenium-trunk") in your system. I have created folder at location
"/Users/admin/" on my machine, here "admin" is the name of the user in my system.

3. Launch terminal and go to the location where this folder exists ("/Users/admin" in my case).

4. Execute the command "svn checkout http://selenium.googlecode.com/svn/trunk selenium-trunk". Folder name
"selenium-trunk" should be modified in case your folder name is different. Note that it might take 5-10
minutes time to complete this command execution.

5. Once the command execution is completed, go to that folder by running command "cd selenium-trunk".

6. Run another command i.e. "./go safari". This command will create a build folder (which contains
WebDriver) inside "selenium-trunk" folder. Remember to add period before "/".

7. Launch "KeyChain Access" application and choose "System" option. By default, "System" folder might be
locked, you should unlock it by giving valid credentials of the logged in user.

8. Drag the Safari Developer Certificate file (which was created in step#1) and drop it into the System
folder in the "Key Chain Access" application.

9. Right-click on the certificate file which is added to the System folder, and choose "Get Info" dialog.

10. Choose "Always Trust" value for all options displayed under "Trust" section.

11. Launch Safari browser and choose "Show Extension Builder" option from "Developer" menu. If Developer
menu is not visible, we can enable this menu by selecting "Show Develop menu in menu bar" check box
from "Advanced" tab of Safari Preferences dialog.

12. Click on "+" button at the left bottom corner of the dialog, and choose "Add Extension" option.
13. Browse to the location where Selenium WebDriver is saved, to me it is "/Users/admin/seleniumtrunk/
build/javascript/safari-driver/SafariDriver.safariextension".

14. Confirm that no errors are shown like "This certificate is not signed" below that extension drop down.

15. Click on Install button.

16. Go to Safari menu -> Preferences option -> Extensions tab.

17. Confirm that WebDriver option is shown in the dialog.
 */
package com.techo2.projectx.testsuite.base;
import org.apache.log4j.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.safari.SafariDriver;

public class SafariBrowser
{
	private static Logger log = Logger.getLogger("SafariBrowser");
	/**
	 * Default SafariDriver setup method
	 * @return SafariDriver instance
	 */
 public static WebDriver init()
 {
	 log.info("Launching safari with new profile");
	 return new SafariDriver();
 }
}
