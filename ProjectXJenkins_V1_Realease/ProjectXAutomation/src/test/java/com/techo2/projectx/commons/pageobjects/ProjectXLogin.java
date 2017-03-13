package com.techo2.projectx.commons.pageobjects;

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.interactions.Actions;
import org.testng.log4testng.Logger;

import com.techo2.projectx.commons.datahandlers.ConfigManager;
import com.techo2.projectx.commons.pageobjects.locators.LoginPageLocators;
import com.techo2.projectx.commons.selenium.SafeActions;

import Utility.util;

public class ProjectXLogin extends SafeActions implements LoginPageLocators{
	
	ConfigManager envprop = new ConfigManager("environment");
	Logger logger= Logger.getLogger(ProjectXLogin.class);
	

	WebDriver loginpage;
	public ProjectXLogin(WebDriver loginpage) {
		super(loginpage);
		this.loginpage = loginpage;
	}
	
	
	/**Method- Login page
	 * @throws InterruptedException */
	
	
	public void login() throws InterruptedException{
		
			
			 // Verify the page title to check if the correct page is launched
			try{
			/*String  pagetitle = loginpage.getTitle();
			if(!pagetitle.equals("ProjectX")){
				logger.info("Launched the incorrect page...");
			
				
			}*/
			
			loginpage.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);
			
			loginpage.findElement(By.xpath("//input[@id='email']")).sendKeys("sravan.gangelly@techo2.com");
			Thread.sleep(5000);
			loginpage.findElement(By.xpath("//input[@id='pass']")).sendKeys("test111");
			Thread.sleep(5000);
			loginpage.findElement(By.xpath("//button[@class='normal bluebutton bluebuttonlarge']")).click();
			Thread.sleep(5000);
			
			
			
		/*	safeType(USERNAME, envprop.getProperty("login_username"),5);
			safeType(PASSWORD, envprop.getProperty("login_password"),5);
			safeClick(SIGNIN_BTN, 5);*/
			
			Thread.sleep(3000);
			
			// clicked on Logout button.So it will exapnd
			
			//safeClick(Logout_Exapnsion, 5);
			loginpage.findElement(By.xpath("//a[@class='dropdown-toggle']")).click();
			util.waitForSeconds(3);
			Actions action = new Actions(loginpage);
			WebElement expansionmousehover = loginpage.findElement(By.xpath("//ul[@class='dropdown-menu']"));
			action.moveToElement(expansionmousehover).moveToElement(loginpage.findElement(By.xpath("//a[text()='Logout']"))).click().build().perform();
			}catch (NullPointerException e) {
				
			}
			
			
		      //Thread.sleep(3000);
		
			/*//Verify that erro message is displayed for Authentication failure 
			
			String invalidloginmessage = loginpage.findElement(By.xpath("//div[@class='alert alert-danger']")).getText();
			if(invalidloginmessage.equals("Invalid Email/Password")){
				logger.info("Correct message is displayed");
			} else {
				logger.info("Incorrect message is displayed");
			}
			*/

		
		
		
	}
		
	
	
	
	
	
	
	

}
