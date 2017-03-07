package com.techo2.projectx.commons.pageobjects.locators;

import org.openqa.selenium.By;

public interface LoginPageLocators {
	
	   // Login page.
	public static By USERNAME =  By.xpath("//input[@id='email']");
	public static By PASSWORD =  By.xpath("//input[@id='pass']");
	public static By SIGNIN_BTN =  By.xpath("//button[@class='normal bluebutton bluebuttonlarge']");
	public static By Logout_Exapnsion = By.xpath("//a[@class='dropdown-toggle']");

}

