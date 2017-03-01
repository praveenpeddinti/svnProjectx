package com.techo2.skiptaneo.commons.excelcases;

import static org.testng.ConversionUtils.wrapDataProvider;

import java.io.IOException;
import java.util.Arrays;
import java.util.Collection;

import org.apache.log4j.BasicConfigurator;
import org.apache.log4j.Logger;
import org.apache.log4j.PropertyConfigurator;
import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.Select;
import org.testng.Assert;
import org.testng.SkipException;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Factory;
import org.testng.annotations.Parameters;
import org.testng.annotations.Test;

import Utility.TestBase;
import Utility.util;

public class RegistrationExcelDataTest extends TestBase {
	
	 Logger logger = Logger.getLogger("RegistrationExcelDataTest");
	 
	 
	 @Factory
		public static Object[] factoryDataSupplier() {
			return wrapDataProvider(RegistrationExcelDataTest.class, dataSupplier());
		}
	 
	 public String Serial;
	 public String Url;
	 public String Firstname;
	 public String Lastname;
	 public String Email;
	 public String Subspeciality;
     public String NPINumber;
     public String SecurityQuestion;
     public String SecurityAnswer;
     public String CreatePassword;
     public String ConfirmPassword;
     public String Iagree;
     public String Result;
     
     
     public RegistrationExcelDataTest(String Serial,String Url,String Firstname,String Lastname, 
    		                           String Email,String Subspeciality,String NPINumber,String SecurityQuestion,
    		                           String SecurityAnswer,String CreatePassword,String ConfirmPassword,String Iagree,String Result){
    	 
    	 
     this.Serial = Serial;
     this.Url = Url;
     this.Firstname = Firstname;
     this.Lastname = Lastname;
     this.Email = Email;
     this.Subspeciality = Subspeciality;
     this.NPINumber = NPINumber;
     this.SecurityQuestion = SecurityQuestion;
     this.SecurityAnswer = SecurityAnswer;
     this.CreatePassword = CreatePassword;
     this.ConfirmPassword = ConfirmPassword;
     this.Iagree = Iagree; 
     this.Result = Result;
     
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
	 
	 
 
    @Test
      public void loginwithValidNPI() throws Exception{
    	 
    	logger.info("Registerd with valid NPI Number");
		if(CONFIG.get("browser").equals("chrome")&& v.equals("52") || CONFIG.get("TestData_FileName").equals(CONFIG.get("ValidNPIData")))
		
		{
			logger.info("Registerd with valid NPI Number");
			throw new SkipException("Skipping - This is not ready for testing ");
		}
		driver.manage().deleteAllCookies();
		String name = util.validnpiUrl("RegistrationExcelDataTest", Serial);
		
		try{
    	driver.get("URL");
    	driver.findElement(By.linkText("New Member Registration")).click();
 		String verifyText = driver.findElement(By.xpath("//h2[@class='pagetitle']")).getText();
 		Assert.assertEquals(verifyText, "Registration");
 		System.out.println("Text dispalyed Registration"+ verifyText);
 		util.waitForSeconds(5);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_firstName']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_firstName']")).sendKeys(Firstname);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_lastName']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_lastName']")).sendKeys(Lastname);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_email']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_email']")).sendKeys(Email);
 		driver.findElement(By.xpath("//select[@id='NewUserRegistrationForm_Subspeciality']")).clear();
 		Select select = new Select(driver.findElement(By.xpath("//select[@id='NewUserRegistrationForm_Subspeciality']")));
 		select.selectByVisibleText(Subspeciality);
 		util.waitForSeconds(5);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_NPINumber']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_NPINumber']")).sendKeys(NPINumber);
 		Select selectsecurityquestion = new Select(driver.findElement(By.xpath("//select[@id='NewUserRegistrationForm_ChallangeQuestion']")));
 		selectsecurityquestion.selectByVisibleText(SecurityQuestion);
 		util.waitForSeconds(5);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_ChallangeQuestionAnswer']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_ChallangeQuestionAnswer']")).sendKeys(SecurityAnswer);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_pass']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_pass']")).sendKeys(CreatePassword);
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_confirmpass']")).clear();
 		driver.findElement(By.xpath("//input[@id='NewUserRegistrationForm_confirmpass']")).sendKeys(ConfirmPassword);
 		driver.findElement(By.xpath("//input[@id='ytNewUserRegistrationForm_termsandconditions']/following-sibling::span")).click();
 		util.waitForSeconds(5);
 		driver.findElement(By.xpath("//input[@id='userregistration']")).click();
		}
		catch(Exception e){
			logger.info("Error message");
			Utility.util.createXLSReport("RegistrationExcelDataTest", Serial);
			
		}
		
		}
 		

    @Parameters
	 public static Collection<Object[]> dataSupplier(){
			Object[][] data = util.getData("RegistrationExcelDataTest");
			return Arrays.asList(data);
	 
	 }
	 
	 
   
	

}
