package com.techo2.projectx.testsuite.base;

import org.openqa.selenium.Capabilities;
import org.openqa.selenium.HasCapabilities;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.support.events.EventFiringWebDriver;



public class MyEventFiringWebDriver extends EventFiringWebDriver implements HasCapabilities {

    private RemoteWebDriver driver;

    public MyEventFiringWebDriver(RemoteWebDriver driver) {
        super(driver);
        this.driver = driver;
    }

    
    
    
    @Override
    public Capabilities getCapabilities() {
        return driver.getCapabilities();
    }

}