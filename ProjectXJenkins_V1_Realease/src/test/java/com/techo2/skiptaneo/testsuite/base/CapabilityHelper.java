package com.techo2.skiptaneo.testsuite.base;

import org.apache.log4j.Logger;
import org.openqa.selenium.Platform;
import org.openqa.selenium.remote.DesiredCapabilities;

import com.techo2.skiptaneo.commons.datahandlers.ConfigManager;


/**
 * Different RemoteWebdriver Capabilities can be constructed by varying BrowserName, Version, Platform.
 * These capabilities are needed to define initialize RemoteWebdriver, which in turn runs your tests on SauceLabs.com or Testingbot.com 
 * This Helper class will define several methods to get the capabilities by reading the Cloud.Browser.Name,
 * Cloud.Browser.Platform, Cloud.Browser.Version config parameters. 
 * e.g Capability is 
 * Browser: Firefox
 * Version: 14
 * Platform :XP
 */


public class CapabilityHelper {

	DesiredCapabilities capabilities;
	ConfigManager sys;
	private Logger log = Logger.getLogger("CapabilityHelper");
	/**
	 * Gets the desiredCapability of respective CloudType(saucelabs or testinbot)
	 * @param CloudType
	 * @return DesiredCapability
	 * @throws Exception
	 */
	public DesiredCapabilities addCapability(String browserType, String CloudType)
	{
		sys = new ConfigManager();
		setBrowser(browserType);
		setOperatingSystem(sys.getProperty("Cloud.Browser.Platform"));
		if(CloudType.equalsIgnoreCase("SauceLabs"))
		{
			capabilities.setCapability("version", sys.getProperty("Cloud.Browser.Version"));
			capabilities.setCapability("name", sys.getProperty("Cloud.TestName"));
		}
		
		else if(CloudType.equalsIgnoreCase("Testingbot"))
		{					
			capabilities.setCapability("version", sys.getProperty("Cloud.Browser.Version"));
			capabilities.setCapability("name", sys.getProperty("Cloud.TestName"));
		}
		else if(CloudType.equalsIgnoreCase("grid"))
		{

		}
		else
		{
			log.error("Type of cloud entered does not exist - "+CloudType);
			log.info("Using grid as cloud type..");		
		}		
		return capabilities;
	}

	/**
	 * 
	 * This method sets the desired capabilities based on browser type
	 *
	 * @param browserType , Need to pass the browser type
	 */
	private void setBrowser(String browserType)
	{
		switch(browserType)
		{
			case "chrome":
				capabilities = DesiredCapabilities.chrome();
				break;
			case "firefox":
				capabilities = DesiredCapabilities.firefox();
				break;
			case "ff":
				capabilities = DesiredCapabilities.firefox();	
				break;
			case "ie":
				capabilities = DesiredCapabilities.internetExplorer();
				capabilities.setCapability("ignoreProtectedModeSettings", true);
				capabilities.setCapability("enablePersistentHover", false);
				capabilities.setCapability("native_events", false);
				break;
			case "iexplore":
				capabilities = DesiredCapabilities.internetExplorer();
				capabilities.setCapability("ignoreProtectedModeSettings", true);
				capabilities.setCapability("enablePersistentHover", false);
				capabilities.setCapability("native_events", false);
				break;				
			case "safari":
				capabilities = DesiredCapabilities.safari();
				break;
			case "opera":	
				capabilities = DesiredCapabilities.opera();
				break;
			default:
				log.error("browser : "+browserType+" is invalid, Launching Firefox as browser of choice..");
				capabilities = DesiredCapabilities.firefox();		
		}
	}
	
	
	/**
	 * Extracted method that does common setup of setting OS/Platform where cloud browser should be launched
	 * @return null
	 */
	private void setOperatingSystem(String platform)
	{
		if(platform.equalsIgnoreCase("xp"))
		{
			capabilities.setCapability("platform", Platform.XP);
		}
		else if(platform.equalsIgnoreCase("Windows7"))
		{
			capabilities.setCapability("platform", Platform.WINDOWS);
		}
	}
	

	
}
