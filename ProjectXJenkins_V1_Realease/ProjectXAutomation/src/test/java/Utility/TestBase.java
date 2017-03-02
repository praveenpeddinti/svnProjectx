package Utility;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Date;
import java.util.Properties;
import java.util.concurrent.TimeUnit;

import org.apache.log4j.BasicConfigurator;
import org.openqa.selenium.By;
import org.openqa.selenium.Capabilities;
import org.openqa.selenium.JavascriptExecutor;
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
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import datatable.Xls_Reader;

public class TestBase {
	
	public static Properties CONFIG=null;
	public static Properties OR=null;
	public static WebDriver dr=null;
	public static Actions builder=null;
	public static EventFiringWebDriver driver=null;
	public static boolean isLoggedIn=false;
	public static Xls_Reader datatable=null;
	public static WebElement element;
	public static String en;
	public static WebDriverWait wait;
	public static String v;
	public static Capabilities cap; 
	
	
	
	public static void  initialize() throws IOException{
		//	if(driver == null){
		
				CONFIG= new Properties();
				//FileInputStream fn = (FileInputStream) CONFIG.getClass().getResourceAsStream(System.getProperty("user.dir")+"//confi.properties");
				FileInputStream fn =new FileInputStream(System.getProperty("user.dir")+"//confi.properties");
				CONFIG.load(fn);
				OR= new Properties();
				fn =new FileInputStream(System.getProperty("user.dir")+"//or.properties");
				OR.load(fn);

				if(CONFIG.getProperty("browser").equals("Firefox")){
					dr = new FirefoxDriver();

				}else if(CONFIG.getProperty("browser").equals("Safari")){
					dr = new SafariDriver();

				}else if(CONFIG.getProperty("browser").equals("IE")){
					File file = new File(System.getProperty("user.dir")+"//src//test//resources//IEDriverServer.exe");
					System.setProperty("webdriver.ie.driver", file.getAbsolutePath());
					DesiredCapabilities caps = DesiredCapabilities.internetExplorer();    
					caps.setCapability(InternetExplorerDriver.INTRODUCE_FLAKINESS_BY_IGNORING_SECURITY_DOMAINS,true);    
					/*DesiredCapabilities caps = DesiredCapabilities.internetExplorer();
					caps.setCapability("ignoreZoomSetting", true);*/
					dr = new InternetExplorerDriver(caps);

				}else if(CONFIG.getProperty("browser").equals("Chrome")){
					File file = new File(System.getProperty("user.dir")+"//src//test//resources///chromedriver.exe");
					System.setProperty("webdriver.chrome.driver", file.getAbsolutePath());
					ChromeOptions options = new ChromeOptions();
					options.setExperimentalOption("excludeSwitches",Arrays.asList("ignore-certificate-errors"));
				//	options.addArguments("--ignore-certificate-errors");
					dr = new ChromeDriver(options);
				}

				driver = new EventFiringWebDriver(dr);
				builder = new Actions(dr);
				driver.manage().window().maximize();
				driver.manage().timeouts().implicitlyWait(15, TimeUnit.SECONDS);
				driver.manage().timeouts().pageLoadTimeout(120, TimeUnit.SECONDS);
				wait = new WebDriverWait(driver,30);
				BasicConfigurator.configure();
				version(v);
			}

	public static By findDynamicElement(By by, int timeOut) {
		WebDriverWait wait1 = new WebDriverWait(driver, timeOut);
	    wait1.until(ExpectedConditions.visibilityOfElementLocated(by));
			return by;		
	}

	public static String folder(String f) throws IOException{
		DateFormat df = new SimpleDateFormat("ddMMyyHHmmss");
		Date dateobj = new Date();
		String timeStamp = df.format(dateobj);
		CONFIG= new Properties();
		FileInputStream fn =new FileInputStream(System.getProperty("user.dir")+"//confi.properties");
		CONFIG.load(fn);
		String fIle=environment(en)+timeStamp;
		File file = new File(System.getProperty("user.dir")+"//screenshots//"+fIle);
		file.mkdir();
		return fIle;
	}
	public static WebElement getObject(String xpathKey) {
		try{
			
			JavascriptExecutor js = (JavascriptExecutor)driver;
			WebElement element= driver.findElement(By.xpath(OR.getProperty(xpathKey)));
			System.out.println(element);
			//Use any locator type using to identify the element
			js.executeScript("arguments[0].setAttribute('style', arguments[1]);",element, "color: red; border: 4px solid red;");
			js.executeScript("arguments[0].setAttribute('style', arguments[1]);",element, "");
			return driver.findElement(By.xpath(OR.getProperty(xpathKey)));
		}catch(Throwable t){
			//Assert.assertTrue(false,""+t);
			System.err.println(t);
			return null;
		}
	}
	
	public static String version(String a){
		String b = "Browser Not Initialized";
		if(driver != null){
			cap = ((RemoteWebDriver) dr).getCapabilities();
			cap.getBrowserName().toLowerCase();
			cap.getPlatform().toString();
			v = cap.getVersion().toString();
		}else
			return b;
		return v;
	}
	public static String environment(String en){
		String envi = CONFIG.getProperty("TestData_FileName");
		System.out.println(envi);
		String environment1[] = envi.split("_");
		en = environment1[0];
			return en;
	}
	public static WebElement LoginGetObject(String xpathKey) {
		try{
			return driver.findElement(By.xpath(OR.getProperty(xpathKey)));
		}catch(Throwable t){
			return null;
		}
	}
	
	
	
	

}
