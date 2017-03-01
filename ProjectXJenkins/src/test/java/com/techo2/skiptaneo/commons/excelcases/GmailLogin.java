
package com.techo2.skiptaneo.commons.excelcases;

import java.io.IOException;
import java.util.Arrays;
import java.util.Collection;

import org.apache.log4j.BasicConfigurator;
import org.apache.log4j.Logger;
import org.apache.log4j.PropertyConfigurator;
import org.openqa.selenium.By;
import org.testng.SkipException;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Factory;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import com.techo2.skiptaneo.testsuite.base.BaseSetup;

import static org.testng.ConversionUtils.wrapDataProvider;


import Utility.TestBase;
import Utility.util;

public class GmailLogin extends TestBase  {
	
	
	Logger  APP_LOGS = Logger.getLogger(GmailLogin.class);
	public String Serial;
	public String Url;
	public String EmailId;
	public String Password;
	public String Result;
	
	public GmailLogin(String Serial,String Url,String EmailId,String Password,String Result){
		
	    this.Serial=Serial;
		this.Url=Url;
		this.EmailId=EmailId;
		this.Password=Password;
		this.Result=Result;
	}
	
	


	@BeforeMethod
	public void BeforeTest() throws IOException{
		initialize();
		PropertyConfigurator.configure("log4j.properties");
		BasicConfigurator.configure();
	}
	
	@AfterMethod
    public void After() throws IOException{
		driver.quit();
	}
	
	@Factory
	public static Object[] factoryDataSupplier() {
		return wrapDataProvider(GmailLogin.class, dataSupplier());
	}
	

	@Parameters
	public static Collection<Object[]> dataSupplier(){
		Object[][] data = util.getData("GmailLogin");
		return Arrays.asList(data);

	}
	
	 
	@Test
	public void gmaiLogin() throws Exception{
		
		try{
		driver.get(Url);
		util.waitForSeconds(5);
		driver.findElement(By.xpath("//input[@id='Email']")).clear();
		driver.findElement(By.xpath("//input[@id='Email']")).sendKeys(EmailId);
		driver.findElement(By.xpath("//input[@id='next']")).click();
		util.waitForSeconds(5);
		driver.findElement(By.xpath("//input[@id='Passwd']")).clear();
		driver.findElement(By.xpath("//input[@id='Passwd']")).sendKeys(Password);
		driver.findElement(By.xpath("//input[@id='signIn']")).click();
		util.waitForSeconds(5);
		Utility.util.createXLSPassReport("GmailLogin", Serial);
	}
	catch(Exception e){
		APP_LOGS.info("Error message");
		Utility.util.createXLSReport("GmailLogin", Serial);
		
	}
	   
	}
	

}
