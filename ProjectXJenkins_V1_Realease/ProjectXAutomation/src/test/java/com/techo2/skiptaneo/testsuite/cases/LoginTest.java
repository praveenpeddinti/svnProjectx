package com.techo2.skiptaneo.testsuite.cases;

import org.openqa.selenium.WebDriver;
import org.testng.annotations.Test;

import com.techo2.skiptaneo.commons.datahandlers.ConfigManager;
import com.techo2.skiptaneo.commons.pageobjects.ProjectXLogin;
import com.techo2.skiptaneo.commons.pageobjects.locators.LoginPageLocators;
import com.techo2.skiptaneo.testsuite.base.BaseSetup;

import Utility.TestBase;

public class LoginTest extends BaseSetup implements LoginPageLocators{
	
	
	ConfigManager envProps = new ConfigManager("environment");
	
	ProjectXLogin projectxlogin;
	WebDriver driver;
	
	@Test
	public void loginwithcredentials() throws InterruptedException{
		
		projectxlogin = new ProjectXLogin(getDriver());
		getDriver().get(envProps.getProperty("url"));
	    projectxlogin.login();	
	   
	}
	
	
	
	
	

}
