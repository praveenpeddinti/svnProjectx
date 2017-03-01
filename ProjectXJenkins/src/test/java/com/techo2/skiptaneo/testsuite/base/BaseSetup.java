/**************************************** PURPOSE **********************************

 - This class contains the code related to Basic setup of TestSuites such as Instantiating Browser,
 - Launching Browser from selected Configuration, perform Clean Up etc

USAGE
 - Inherit this BaseClass for any TestSuite class. You don't have to write any @Beforeclass and @AfterClass
 - actions in your TestSuite Classes
 
 - Example: 
 --import Com.Base
 --- public class <TestSuiteClassName> extends BaseClass
*/
package com.techo2.skiptaneo.testsuite.base;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.util.Arrays;
import java.util.Properties;
import java.util.concurrent.TimeUnit;

import org.apache.log4j.BasicConfigurator;
import org.apache.log4j.Logger;
import org.openqa.selenium.By;
import org.openqa.selenium.Capabilities;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.ie.InternetExplorerDriver;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.safari.SafariDriver;
import org.openqa.selenium.support.events.EventFiringWebDriver;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.ITestContext;
import org.testng.Reporter;
import org.testng.SkipException;
import org.testng.annotations.AfterClass;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.AfterSuite;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.BeforeSuite;
import org.testng.annotations.Listeners;

import com.techo2.skiptaneo.commons.datahandlers.ConfigManager;
import com.techo2.skiptaneo.commons.datahandlers.ExcelManager;

import com.techo2.skiptaneo.commons.listiners.TestListener;
import com.techo2.skiptaneo.commons.listiners.WebListener;
import com.techo2.skiptaneo.commons.selenium.SafeActions;
import com.techo2.skiptaneo.commons.selenium.Sync;
import com.techo2.skiptaneo.commons.utilities.ReportSetup;
import com.techo2.skiptaneo.commons.utilities.TimeOuts;
import com.techo2.skiptaneo.commons.utilities.UtilityMethods;

import datatable.Xls_Reader;


//Listeners has been added.

@Listeners({ TestListener.class})
public class BaseSetup implements TimeOuts
{
	public WebDriver driver;
	private boolean isReportFolderCreated = true;
	private Logger log = Logger.getLogger(BaseSetup.class);	
	private MyEventFiringWebDriver efDriver;
	public ConfigManager sys = new ConfigManager();
	public ConfigManager app = new ConfigManager("App");
	public ConfigManager test = new ConfigManager("TestDependency");
	public ConfigManager environment = new ConfigManager("environment");
	//public LoginExcelManager loginexcelmanager = new LoginExcelManager("Logindata");
	

	
	public static Properties CONFIG=null;
	public static Properties OR=null;
	public static WebDriver dr=null;
	public static Actions builder=null;
	
	public static boolean isLoggedIn=false;
	public static Xls_Reader datatable=null;
	public static WebElement element;
	public static String en;
	public static WebDriverWait wait;
	public static String v;
	public static Capabilities cap; 
	
	

	

	/**
	 * Getter method for WebDriver
	 * @return driver
	*/
    public WebDriver getDriver() 
    {
        return driver;
    }

    /**
     * 
     * Setter method for WebDriver
     *
     * @param driver
     */
    public void setDriver(WebDriver driver) 
    {
        this.driver = driver;
    }
    
    /**
     * 
     * Creates folder structure to store the automation reports
     *
     * @throws Exception
     */
    @BeforeSuite
    public void beforeSuite() throws Exception
    {
		if (isReportFolderCreated)
		{			
			ReportSetup.createFolderStructure();
			isReportFolderCreated = false;
		}
		log.info("<h2>--------------------SuiteRunner Log-------------------------<h2>");
    }
    
    /**
     * Method initialize() is declared as part of @BeforeClass
     * If BaseClass.java is inherited from any TestSuite class, the initialization will happen automatically
     * The initialization() process includes read the Browser name parameter from "Config.Properties" file and launch the selected browser and navigate to the given URL
     * @throws Exception
     */
    
    @BeforeClass (groups = { "regression" })
	public void initializeBaseSetup(ITestContext context)
	{
    	try
    	{
    		String browserType = environment.getProperty("browser");
    		String url = environment.getProperty("url");
	    	initiateDriver(browserType);
	    	log.info("Initiated Webdriver...");
	    	context.setAttribute("driver", driver);		
			driver.manage().window().maximize();
			setPageLoadTimeOut(VERYLONGWAIT);
			(new Sync(driver)).setImplicitWait(IMPLICITWAIT);
			driver.get(url);
    	}
    	catch (Exception e)
    	{		    		
       		log.error(e.getMessage() +"---"+UtilityMethods.getStackTrace());
    	}
	}



    /**
     * Purpose - to initiate driver based on the browser
     * @return - driver
     */
	public void initiateDriver(String browserType) 
	{		
		log.info("Browser name present in config file :" + browserType);		   				
		if(sys.getProperty("ModeOfExecution").equalsIgnoreCase("remote"))
		{
			log.info("-----------------STARTED RUNNING SELENIUM TESTS ON CLOUD /GRID------------------");
			setDriver(new RemoteDriver().init(browserType));
		}
		else if(sys.getProperty("ModeOfExecution").equalsIgnoreCase("linear"))
		{
			log.info("-----------------STARTED RUNNING SELENIUM TESTS ON LOCAL MACHINE------------------");
			setDriver(browserType);				
		}				
		else
		{
			log.error("Enter valid Execution Type i.e. Linear/Remote");
			log.info("Running tests in Linear Mode");
			log.info("-----------------STARTED RUNNING SELENIUM TESTS ON LOCAL MACHINE------------------");
			setDriver(browserType);	
		}
		registerWebDriverListener();
		log.info("what driver is running? "+getBrowserName());
		sys.writeProperty("CurrentlyRunningBrowserName",getBrowserName());
	}


	/**
	 * Purpose - Registers the web driver instance with EventFiringWebDriver
	 * @param driver
	 * @return - Returns the EventFiringWebDriver Instance to respective Browser Instance type
	 */
	private void registerWebDriverListener() 
	{
		efDriver = new MyEventFiringWebDriver((RemoteWebDriver) driver);
		WebListener webListener = new WebListener();
		driver = efDriver.register(webListener);
	}
	
	/**
	 * 
	 * This method sets the driver object based on browser name. If invalid browser name is passed, by default it'll set forefox browser
	 *
	 * @param browserType , Need to pass the browser type
	 */
	String browerType="";
	private void setDriver(String browserType)
	{
		switch(browserType)
		{
			case "chrome":
				driver = ChromeBrowser.init();
				break;
			case "firefox":
				driver = FirefoxBrowser.init();
				break;
			case "ff":
				driver = FirefoxBrowser.init();
				break;
			case "ie":
				driver = IeBrowser.init();
				break;
			case "iexplore":
				driver = IeBrowser.init();
				break;
			case "safari":
				driver = SafariBrowser.init();
				break;
			default:
				log.error("browser : "+browserType+" is invalid, Launching Firefox as browser of choice..");
				driver = FirefoxBrowser.init();		
		}
	}
    
    /**
     * This method since added in "AfterClass" group and when this class is inherited from a TestSuite class, it will be called automatically
     * @throws Exception
     */
    @AfterClass (groups = { "regression" },alwaysRun=true)
	public void CloseBrowser() throws Exception
	{       
    	if(driver != null)
    	{
    		//driver.quit();
    	}
	}
    
    /**
     * 
     * This method adds Log file link to ReportNG report
     *
     * @throws Exception
     */
    @AfterSuite
    public void AddLogFileToReport() throws Exception
    {
    	log.info("after suite");
    	String sSeperator =  UtilityMethods.getFileSeperator();    	
		String logFilePath = ".." + sSeperator + "Log.log"; 
		Reporter.log("<br>");
    	Reporter.log("<a class=\"cbutton\" href=\""+logFilePath+"\">Click to Open Log File</a>");
    	String PageLoadTimeSummaryFilePath = ".." + sSeperator + "PageLoadTime_Summary.html"; 
    	File f = new File(PageLoadTimeSummaryFilePath);
    	if(f.exists())
    	{
    		Reporter.log("<br>");
    		Reporter.log("<a class=\"cbutton\" href=\""+PageLoadTimeSummaryFilePath+"\">Click to Open PageLoad Time Summary File</a>");
    	}
    }
    
	/**
	 * 
	 * This method sets page load timeout.
	 *
	 * @param timeOut , Need to pass the time in seconds  
	 */
    public void setPageLoadTimeOut(int timeOut)
    {
		/* Except for Chrome Browser, set the default Page load time out to 200 seconds maximum. 
		 * There was a known issue with setting PageloadTimeout for Chrome browser. This still needs to be investigated
		 */
		if(!(isSafariBrowser() || isChromeBrowser()))
		{
			driver.manage().timeouts().pageLoadTimeout(timeOut, TimeUnit.SECONDS);					
		}	
    }
    
    /**
     * 
     * This method is used to know whether the dependent test has passed or not
     *
     * @param dependentTestName , Need to pass the dependent test name
     * @throws SkipException
     */
    public void hasDependentTestMethodPassed(String dependentTestName) throws SkipException
    {
    	String currentTestName = Thread.currentThread().getStackTrace()[2].getMethodName();    	
		if(test.getProperty(dependentTestName)!=null)
		{
			if(test.getProperty(dependentTestName).equalsIgnoreCase("pass"))
			{
				log.info("dependent test - "+dependentTestName+" has passed \n Running test - "+currentTestName);
			}
			else
			{
				log.info("dependent test - "+dependentTestName+" has failed \n Hence test - "+currentTestName+"is skipped");
				throw new SkipException("Dependent test - "+dependentTestName+" has failed \n Hence test - "+currentTestName+"is skipped");
			}
		}
		else
		{
			log.info("dependent test - "+dependentTestName+" did not run \n Hence test - "+currentTestName+"is skipped");
			throw new SkipException("Dependent test - "+dependentTestName+" did not run \n Hence test - "+currentTestName+"is skipped");
		}

    }

    /**
     * 
     * This method is used to verify the specified browser is safari or not
     *
     * @return, returns true if the specified browser is safari browser, else returns false
     */
	public boolean isSafariBrowser()
	{
		return getBrowserName().equalsIgnoreCase("safari");
	}
	
	/**
     * 
     * This method is used to verify the specified browser is chrome or not
     *
     * @return, returns true if the specified browser is chrome browser, else returns false
     */
	public boolean isChromeBrowser()
	{
		return getBrowserName().equalsIgnoreCase("chrome");
	}
	
	/**
	 * 
	 * This method is used to get current browser name
	 *
	 * @return, Returns current browser name
	 */
	public String getBrowserName()
	{
		Capabilities caps = efDriver.getCapabilities();
		return caps.getBrowserName();
	}
	
	
	
	

}
