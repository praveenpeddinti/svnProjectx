package com.techo2.skiptaneo.testsuite.base;



import java.net.URL;

import org.apache.log4j.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.remote.Augmenter;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.openqa.selenium.remote.LocalFileDetector;
import org.openqa.selenium.remote.RemoteWebDriver;

//import org.testng.Assert;
import com.techo2.skiptaneo.commons.datahandlers.ConfigManager;
import com.techo2.skiptaneo.commons.testng.Assert;


/**
 * This class is used to initialize RemoteDriver on cloud. The cloud instance is of Saucelabs or testingbot, depends
 * on the value set in "Cloud.type" under config.properties 
 */
public class RemoteDriver
{
	ConfigManager sys;
	WebDriver driver;
	private Logger log = Logger.getLogger("RemoteDriver");
	DesiredCapabilities capabilities;
	public RemoteDriver() 
	{
		
	}
	
	
	/**
	 * Gets remote driver by setting URL and capabilities - instance of remote driver of testingbot.com or saucelabs.com
	 * @return WebDriver configured on Saucelabs.com or testingbot.com 
	 * @throws Exception
	 */
	public WebDriver init(String browserType)
	{
		try
		{
			sys=new ConfigManager();
			Augmenter aug = new Augmenter();
			driver = new RemoteWebDriver(new URL(sys.getProperty("Cloud.Host.URL")), new CapabilityHelper().addCapability(browserType,sys.getProperty("Cloud.Type")));
			((RemoteWebDriver) driver).setFileDetector(new LocalFileDetector());
			driver = aug.augment(driver);
			 log.info("************CLOUD/GRID REMOTEDRIVER DETAILS********************");
		}
		catch(Exception e)
		{
			e.printStackTrace();
			Assert.fail(e.getMessage());
		}
		return driver;
	}	
}
