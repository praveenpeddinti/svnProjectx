package Utility;

import java.io.FileInputStream;
import java.util.ArrayList;
import java.util.Properties;
import java.util.concurrent.TimeUnit;

import org.openqa.selenium.JavascriptExecutor;

import datatable.Xls_Reader;

public class util extends TestBase{
	
	public static Properties exconf =getProperties();
	public static ArrayList<String> resultSet;
	public static String filename = System.getProperty("user.dir")+"/"+exconf.getProperty("TestData_FileName");
    private static JavascriptExecutor js;
	static String pageLoadStatus = null;
	public static String url;
	public static String fileValue;
	public static String path;
	
	
	
	
	public static Properties getProperties()
	{ 
		Properties prop=null;
		try {
			prop= new Properties();
			//String configfilename = "confi.properties";
			//FileInputStream fn =new FileInputStream("configfilename");
			FileInputStream fn =new FileInputStream(System.getProperty("user.dir")+"\\confi.properties");
			
			prop.load(fn);
			return prop;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
		
	}
	
	public static Object[][] getData(String testName){
		System.out.println("Obj = "+testName);
		if(datatable == null){
			datatable = new Xls_Reader(filename);
		}
		int rows=datatable.getRowCount(testName)-1;
		if(rows <=0){
			Object[][] testData =new Object[1][0];
			return testData;
		}
		rows = datatable.getRowCount(testName);  // 3
		int cols = datatable.getColumnCount(testName);
		System.out.println("Test Name -- "+testName);
		System.out.println("total rows -- "+ rows);
		System.out.println("total cols -- "+cols);
		Object data[][] = new Object[rows-1][cols];
		for(int rowNum = 2 ; rowNum <= rows ; rowNum++){
			for(int colNum=0 ; colNum< cols; colNum++){
				resultSet = new ArrayList<String>();
				data[rowNum-2][colNum]=datatable.getCellData(testName, colNum, rowNum);
				//		resultSet.add(testName);
			}

		}
		//	return getData(testName);
		return data;
	}
	
	
	//pageload status
		public static void waitForPageToLoad() {
			do {
				js = (JavascriptExecutor) driver;
				pageLoadStatus = (String)js.executeScript("return document.readyState");
				System.out.print(".");
			} while ( !pageLoadStatus.equals("complete") );
			System.out.println();
			System.out.println("Page Loaded.");
		}
		
		public static void waitForSeconds(int number) {
			try {
				Thread.sleep(TimeUnit.SECONDS.toMillis(number));
			} catch (InterruptedException ie) {
				System.out.println(ie.getMessage());
			}
		}
		
		public static void createXLSReport(String testName, String serial) throws InterruptedException{
			datatable = new Xls_Reader(filename);
			for(int rownum=2; rownum<=datatable.getRowCount(testName); rownum++){
				for(int colnum=1; colnum<=datatable.getColumnCount(testName); colnum++){
					datatable.getCellData(testName, "Serial", rownum);
					if(datatable.getCellData(testName, "Serial", rownum).equals(serial)){
						datatable.getCellData(testName, colnum, rownum);
						datatable.setCellData(testName, "Result", rownum,"FAIL" );
					}
				}
			}
		}

		public static void createXLSPassReport(String testName, String serial){
			datatable = new Xls_Reader(filename);
			for(int rownum=2; rownum<=datatable.getRowCount(testName); rownum++){
				for(int colnum=1; colnum<=datatable.getColumnCount(testName); colnum++){
					datatable.getCellData(testName, "Serial", rownum);
					if(datatable.getCellData(testName, "Serial", rownum).equals(serial)){
						datatable.getCellData(testName, colnum, rownum);
						datatable.setCellData(testName, "Result", rownum,"PASS" );
					}
				}
			}
		}
	
		public static String readUrl(String testName, String serial) throws InterruptedException{
			datatable = new Xls_Reader(filename);
			for(int rownum=2; rownum<=datatable.getRowCount(testName); rownum++){
				for(int colnum=1; colnum<=datatable.getColumnCount(testName); colnum++){
					datatable.getCellData(testName, "Serial", rownum);
					if(datatable.getCellData(testName, "Serial", rownum).equals(serial)){
						datatable.getCellData(testName, colnum, rownum);
						url = datatable.getCellData(testName, "GmailLogin", rownum);
					}
				}
			}
			return url;
		}
		
		public static String validnpiUrl(String testName, String serial) throws InterruptedException{
			datatable = new Xls_Reader(filename);
			for(int rownum=2; rownum<=datatable.getRowCount(testName); rownum++){
				for(int colnum=1; colnum<=datatable.getColumnCount(testName); colnum++){
					datatable.getCellData(testName, "Serial", rownum);
					if(datatable.getCellData(testName, "Serial", rownum).equals(serial)){
						datatable.getCellData(testName, colnum, rownum);
						url = datatable.getCellData(testName, "ValidNPIData", rownum);
					}
				}
			}
			return url;
		}

		
		
}
