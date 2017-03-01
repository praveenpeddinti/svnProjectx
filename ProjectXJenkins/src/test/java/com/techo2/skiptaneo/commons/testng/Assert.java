package com.techo2.skiptaneo.commons.testng;

/**
 * Assertion tool class. Presents assertion methods with a more natural
 * parameter order. The order is always <B>actualValue</B>, <B>expectedValue</B>
 * [, message]. */

import static org.testng.internal.EclipseInterface.ASSERT_LEFT;
import static org.testng.internal.EclipseInterface.ASSERT_LEFT2;
import static org.testng.internal.EclipseInterface.ASSERT_MIDDLE;
import static org.testng.internal.EclipseInterface.ASSERT_RIGHT;

import java.lang.reflect.Array;
import java.util.Arrays;
import java.util.Collection;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;

import org.apache.log4j.Logger;
import org.testng.collections.Lists;

import com.techo2.skiptaneo.commons.utilities.UtilityMethods;

public class Assert {

	/**
	 * Protect constructor since it is a static only class
	 */
	protected Assert() {
		// hide constructor
	}

	private static Logger log = Logger.getLogger("Assert");
	public static boolean haultonfailure = true;
	private static final String SEPARATOR = " @ ";
	static private Map<String, String> map = null;


	private static String message(String msg, String expected, String actual) {
		return "\n" + msg + " assertion failed Expected [" + expected
				+ "] But Actual [" + actual + "]";
		// "\n"+msg+" assertion failed Expected ["+map.get(msg).split(",")[0]+"] But Actual ["+map.get(msg).split(",")[1]+"]";
	}

	private static String message(String msg, Collection expected,
			Collection actual) {
		return "\n" + msg + " assertion failed Expected [" + expected
				+ "] But Actual [" + actual + "]";
	}

	private static String message(String msg, Iterator expected, Iterator actual) {
		return "\n" + msg + " assertion failed Expected [" + expected
				+ "] But Actual [" + actual + "]";
	}

	private static String message(String msg, Iterable expected, Iterable actual) {
		return "\n" + msg + " assertion failed Expected [" + expected
				+ "] But Actual [" + actual + "]";
	}

	private static String message(String msg, Object[] expected, Object[] actual) {
		return "\n" + msg + " assertion failed Expected [" + expected
				+ "] But Actual [" + actual + "]";
	}

	/**
	 * Asserts that a condition is true. If it isn't, an AssertionError, with
	 * the given message, is thrown if haultonfailure.
	 * 
	 * @param condition
	 *            the condition to evaluate
	 * @param message
	 *            the assertion error message
	 */

	static public void assertTrue(boolean condition, String message) {
		if (!condition) {
			log.error(message(message, Boolean.TRUE.toString(),
					String.valueOf(condition)));
			if (haultonfailure) {
				failNotEquals(Boolean.valueOf(condition), Boolean.TRUE, message);
			} else {

				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						Boolean.TRUE + SEPARATOR + condition);
			}
		}
	}

	public static String doAssert() {
		haultonfailure = true;
		String message = "";
		if (null != map) {
			if (map!=null && map.size() > 0) {
//				Reporter.log("failed asserts : "+map.size());
				message=message+"failed asserts : "+map.size()+"\n";
//				System.out.println("failed asserts:" + map.size());
				int i=1;
				for (String msg : map.keySet()) {
					message = message+"<br>"+
							message(msg.split(SEPARATOR)[0], map.get(msg)
									.split(SEPARATOR)[0],
									map.get(msg).split(SEPARATOR)[1]);
					i++;
				}

			}
			map.clear();
			map=null;
		}
		return message;
	}

	public static void assertAll() {
		String msg=doAssert();
		if(msg.length()>0 && !(msg.equals("")))
		{
			throw new AssertionError(msg);
		}

	}

	/**
	 * Asserts that a condition is true. If it isn't, an AssertionError is
	 * thrown if haultonfailure.
	 * 
	 * @param condition
	 *            the condition to evaluate
	 */
	static public void assertTrue(boolean condition) {
		assertTrue(condition, null);
	}

	
    /*public static void assertTrue(String actualErrormessage, String expectedErrormessage) {
		assertTrue(actualErrormessage, expectedErrormessage);
		
	}*/
	
	/**
	 * Asserts that a condition is false. If it isn't, an AssertionError, with
	 * the given message, is thrown if haultonfailure.
	 * 
	 * @param condition
	 *            the condition to evaluate
	 * @param message
	 *            the assertion error message
	 */
	static public void assertFalse(boolean condition, String message) {
		if (condition) {
			log.error(message(message, Boolean.FALSE.toString(),
					String.valueOf(condition)));
			if (haultonfailure) {
				failNotEquals(Boolean.valueOf(condition), Boolean.FALSE,
						message);
			} else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						Boolean.FALSE + SEPARATOR + condition);
			}
		}
	}

	/**
	 * Asserts that a condition is false. If it isn't, an AssertionError is
	 * thrown if haultonfailure.
	 * 
	 * @param condition
	 *            the condition to evaluate
	 */
	static public void assertFalse(boolean condition) {
		assertFalse(condition, null);
	}

	/**
	 * Fails a test with the given message and wrapping the original exception.
	 *
	 * @param message
	 *            the assertion error message
	 * @param realCause
	 *            the original exception
	 */
	static private void fail(String message, Throwable realCause) {
		AssertionError ae = new AssertionError(message);
		ae.initCause(realCause);

		throw ae;
	}

	/**
	 * Fails a test with the given message.
	 * 
	 * @param message
	 *            the assertion error message
	 */
	static public void fail(String message) {
//		log.error("\n AssertionError" + message);
		throw new AssertionError(message);
	}

	/**
	 * Fails a test with no message.
	 */
	static private void fail() {
		fail(null);
	}

	/**
	 * Asserts that two objects are equal. If they are not, an AssertionError,
	 * with the given message, is thrown if haultonfailure.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */

	static public void assertEquals(Object actual, Object expected,
			String message) {

		if ((expected == null) && (actual == null)) {
			return;
		} else if ((expected != null && actual == null)||(expected == null && actual != null)) {
//			log.error(message(message, expected.toString(), actual.toString()));
			failnotequals(actual, expected, message);
		}

		else {
			if (expected.getClass().isArray()) {
				assertArrayEquals(actual, expected, message);
				return;
			} else if (expected.equals(actual)) {
				return;
			}
		}

		failnotequals(actual, expected, message);

		

	}

	static private void failnotequals(Object actual, Object expected,
			String message) {
		log.error(message(message, expected.toString(), actual.toString()));
		if (haultonfailure) {
			failNotEquals(actual, expected, message);
		} else {

			if (map == null) {
				map = new LinkedHashMap<String, String>();
			}
			map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(), expected
					+ SEPARATOR + actual);
		}
	}

	
	/**
	 * Asserts that two objects are equal. It they are not, an AssertionError,
	 * with given message, is thrown if haultonfailure.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value (should be an non-null array value)
	 * @param message
	 *            the assertion error message
	 */
	private static void assertArrayEquals(Object actual, Object expected,
			String message) {
		// is called only when expected is an array
		if (actual.getClass().isArray()) {
			int expectedLength = Array.getLength(expected);
			if (expectedLength == Array.getLength(actual)) {
				for (int i = 0; i < expectedLength; i++) {
					Object _actual = Array.get(actual, i);
					Object _expected = Array.get(expected, i);
					try {
						assertEquals(_actual, _expected);
					} catch (AssertionError ae) {
						log.error(message(message, expected.toString(),
								actual.toString()));
						if (haultonfailure) {
							failNotEquals(actual, expected, message);
						} else {

							if (map == null) {
								map = new LinkedHashMap<String, String>();
							}
							map.put(message == null ? "" : message+ " (values at index " + i
									+ " are not the same)" + SEPARATOR+ System
									.currentTimeMillis(), expected+ SEPARATOR + actual);
							return;
						}
					}
				}
				// array values matched
				return;
			} else {
				log.error(message(message, expected.toString(),
						actual.toString()));
				if (haultonfailure) {
					failNotEquals(actual, expected, message);
				} else {

					if (map == null) {
						map = new LinkedHashMap<String, String>();
					}
					map.put(message == null ? "" : message
							+ " (Array lengths are not the same)" + SEPARATOR
							+ UtilityMethods.getRandomNumber()+System.currentTimeMillis(), expectedLength
							+ SEPARATOR + Array.getLength(actual));
				}
			}
		}
		log.error(message(message, expected.toString(), actual.toString()));
		if (haultonfailure) {
			failNotEquals(actual, expected, message);
		} else {

			if (map == null) {
				map = new LinkedHashMap<String, String>();
			}
			map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(), expected
					+ SEPARATOR + actual);
		}

	}

	/**
	 * Asserts that two objects are equal. If they are not, an AssertionError is
	 * thrown if haultonfailure.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(Object actual, Object expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two Strings are equal. If they are not, an AssertionError,
	 * with the given message, is thrown if haultonfailure.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(String actual, String expected,
			String message) {
		assertEquals((Object) actual, (Object) expected, message);
	}

	/**
	 * Asserts that two Strings are equal. If they are not, an AssertionError is
	 * thrown if haultonfailure.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(String actual, String expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two doubles are equal concerning a delta. If they are not,
	 * an AssertionError, with the given message, is thrown. If the expected
	 * value is infinity then the delta value is ignored.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param delta
	 *            the absolute tolerable difference between the actual and
	 *            expected values
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(double actual, double expected,
			double delta, String message) {
		// handle infinity specially since subtracting to infinite values gives
		// NaN and the
		// the following test fails
		if (Double.isInfinite(expected)) {
			if (!(expected == actual)) {
				failnotequals(new Double(actual), new Double(expected), message);
			}
		} else if (!(Math.abs(expected - actual) <= delta)) { 
			// Because comparison with NaN always returns false
			failnotequals(new Double(actual), new Double(expected), message);
		}
	}

	/**
	 * Asserts that two doubles are equal concerning a delta. If they are not,
	 * an AssertionError is thrown. If the expected value is infinity then the
	 * delta value is ignored.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param delta
	 *            the absolute tolerable difference between the actual and
	 *            expected values
	 */
	static public void assertEquals(double actual, double expected, double delta) {
		assertEquals(actual, expected, delta, null);
	}

	/**
	 * Asserts that two floats are equal concerning a delta. If they are not, an
	 * AssertionError, with the given message, is thrown. If the expected value
	 * is infinity then the delta value is ignored.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param delta
	 *            the absolute tolerable difference between the actual and
	 *            expected values
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(float actual, float expected, float delta,
			String message) {
		// handle infinity specially since subtracting to infinite values gives
		// NaN and the
		// the following test fails
		if (Float.isInfinite(expected)) {
			if (!(expected == actual)) {
				failnotequals(new Float(actual), new Float(expected), message);
			}
		} else if (!(Math.abs(expected - actual) <= delta)) {
			failnotequals(new Float(actual), new Float(expected), message);
		}
	}

	/**
	 * Asserts that two floats are equal concerning a delta. If they are not, an
	 * AssertionError is thrown. If the expected value is infinity then the
	 * delta value is ignored.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param delta
	 *            the absolute tolerable difference between the actual and
	 *            expected values
	 */
	static public void assertEquals(float actual, float expected, float delta) {
		assertEquals(actual, expected, delta, null);
	}

	/**
	 * Asserts that two longs are equal. If they are not, an AssertionError,
	 * with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(long actual, long expected, String message) {
		assertEquals(Long.valueOf(actual), Long.valueOf(expected), message);
	}

	/**
	 * Asserts that two longs are equal. If they are not, an AssertionError is
	 * thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(long actual, long expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two booleans are equal. If they are not, an AssertionError,
	 * with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(boolean actual, boolean expected,
			String message) {
		assertEquals(Boolean.valueOf(actual), Boolean.valueOf(expected),
				message);
	}

	/**
	 * Asserts that two booleans are equal. If they are not, an AssertionError
	 * is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(boolean actual, boolean expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two bytes are equal. If they are not, an AssertionError,
	 * with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(byte actual, byte expected, String message) {
		assertEquals(Byte.valueOf(actual), Byte.valueOf(expected), message);
	}

	/**
	 * Asserts that two bytes are equal. If they are not, an AssertionError is
	 * thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(byte actual, byte expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two chars are equal. If they are not, an
	 * AssertionFailedError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(char actual, char expected, String message) {
		assertEquals(Character.valueOf(actual), Character.valueOf(expected),
				message);
	}

	/**
	 * Asserts that two chars are equal. If they are not, an AssertionError is
	 * thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(char actual, char expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two shorts are equal. If they are not, an
	 * AssertionFailedError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(short actual, short expected, String message) {
		assertEquals(Short.valueOf(actual), Short.valueOf(expected), message);
	}

	/**
	 * Asserts that two shorts are equal. If they are not, an AssertionError is
	 * thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(short actual, short expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two ints are equal. If they are not, an
	 * AssertionFailedError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(int actual, int expected, String message) {
		assertEquals(Integer.valueOf(actual), Integer.valueOf(expected),
				message);
	}

	/**
	 * Asserts that two ints are equal. If they are not, an AssertionError is
	 * thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(int actual, int expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two collections contain the same elements in the same order.
	 * If they do not, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(Collection<?> actual, Collection<?> expected) {// ///////////////////////////////
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two collections contain the same elements in the same order.
	 * If they do not, an AssertionError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(Collection<?> actual,
			Collection<?> expected, String message) {
		if (actual == expected) {
			return;
		}

		if (actual == null || expected == null) {
			log.error(message(message, actual, expected));

			if (message == null) {
				message = "Collections not equal:";
			}
			if (haultonfailure)
				fail(message);
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		assertEquals(actual.size(), expected.size(), message
				+ ": lists don't have the same size");
		
//		if(actual.size() != expected.size())return;

		Iterator<?> actIt = actual.iterator();
		Iterator<?> expIt = expected.iterator();
		int i = -1;
		while (actIt.hasNext() && expIt.hasNext()) {
			i++;
			Object e = expIt.next();
			Object a = actIt.next();
			String explanation = "Lists differ at element [" + i + "]: " + e
					+ " != " + a;
			String errorMessage = message == null ? explanation : message
					+ ": " + explanation;

			assertEquals(a, e, errorMessage);
		}
	}

	/**
	 * Asserts that two iterators return the same elements in the same order. If
	 * they do not, an AssertionError is thrown. Please note that this assert
	 * iterates over the elements and modifies the state of the iterators.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(Iterator<?> actual, Iterator<?> expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two iterators return the same elements in the same order. If
	 * they do not, an AssertionError, with the given message, is thrown. Please
	 * note that this assert iterates over the elements and modifies the state
	 * of the iterators.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(Iterator<?> actual, Iterator<?> expected,
			String message) {
		if (actual == expected) {
			return;
		}
		if (actual == null || expected == null) {
			log.error(message(message, actual, expected));

			if (message == null) {
				message = "Iterators not equal:";
			}
			if (haultonfailure)
				fail(message+"\n");
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		int i = -1;
		while (actual.hasNext() && expected.hasNext()) {

			i++;
			Object e = expected.next();
			Object a = actual.next();
			String explanation = "Iterators differ at element [" + i + "]: "
					+ e + " != " + a;
			String errorMessage = message == null ? explanation : message
					+ ": " + explanation;

			assertEquals(a, e, errorMessage);

		}

		if (actual.hasNext()) {

			String explanation = "Actual iterator returned more elements than the expected iterator.";
			String errorMessage = message == null ? explanation : message
					+ ": " + explanation;
			if (haultonfailure)
				fail(message+"\n");
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}

		} else if (expected.hasNext()) {

			String explanation = "Expected iterator returned more elements than the actual iterator.";
			String errorMessage = message == null ? explanation : message
					+ ": " + explanation;
			if (haultonfailure)
				fail(message+"\n");
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}

		}

	}

	/**
	 * Asserts that two iterables return iterators with the same elements in the
	 * same order. If they do not, an AssertionError is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(Iterable<?> actual, Iterable<?> expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two iterables return iterators with the same elements in the
	 * same order. If they do not, an AssertionError, with the given message, is
	 * thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(Iterable<?> actual, Iterable<?> expected,
			String message) {
		if (actual == expected) {
			return;
		}
		if (actual == null || expected == null) {
			log.error(message(message, expected, actual));

			if (message == null) {
				message = "Iterables not equal:";
			}
			if (haultonfailure)
				fail(message);
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		Iterator<?> actIt = actual.iterator();
		Iterator<?> expIt = expected.iterator();

		assertEquals(actIt, expIt, message);
	}

	/**
	 * Asserts that two arrays contain the same elements in the same order. If
	 * they do not, an AssertionError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(Object[] actual, Object[] expected,
			String message) {
		if (actual == expected) {
			return;
		}

		if ((actual == null && expected != null)
				|| (actual != null && expected == null)) {
			log.error(message(message, expected, actual));

			if (message == null) {
				message = "Iterables not equal:";
			}
			if (haultonfailure)
				fail(message);
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}
		assertEquals(Arrays.asList(actual), Arrays.asList(expected), message);
	}

	/**
	 * Asserts that two arrays contain the same elements in no particular order.
	 * If they do not, an AssertionError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEqualsNoOrder(Object[] actual, Object[] expected, String message) {
		if (actual == expected) {
			return;
		}

		if ((actual == null && expected != null)
				|| (actual != null && expected == null)) {
			if (message == null)
				message = "Arrays not equal: " + Arrays.toString(expected)
						+ " and " + Arrays.toString(actual);
			log.error(message(message, actual, expected));
			if (haultonfailure)	fail(message(message, expected, actual));
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		if (actual.length != expected.length) {
			if (message == null)
				message = "Arrays do not have the same size:"+ actual.length + " != " + expected.length;
			log.error(message(message, expected, actual));
			if (haultonfailure)	fail(message(message, String.valueOf(expected.length),  String.valueOf(actual.length)));
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		List<Object> actualCollection = Lists.newArrayList();// need to look into src
		for (Object a : actual) {
			actualCollection.add(a);
		}
		for (Object o : expected) {
			actualCollection.remove(o);
		}
		if (actualCollection.size() != 0) {
			if (message == null)
				message = "Arrays not equal: " + Arrays.toString(expected)+ " and " + Arrays.toString(actual);
			log.error(message(message, expected, actual));
			if (haultonfailure)	fail(message(message, String.valueOf(Arrays.toString(expected)),  Arrays.toString(actual)));
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}
	}

	static public void assertEquals(Object[] actual, Object[] expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Asserts that two arrays contain the same elements in no particular order.
	 * If they do not, an AssertionError is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEqualsNoOrder(Object[] actual, Object[] expected) {
		assertEqualsNoOrder(actual, expected, null);
	}

	/**
	 * Asserts that two arrays contain the same elements in the same order. If
	 * they do not, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static public void assertEquals(final byte[] actual, final byte[] expected) {
		assertEquals(actual, expected, "");
	}

	/**
	 * Asserts that two arrays contain the same elements in the same order. If
	 * they do not, an AssertionError, with the given message, is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static public void assertEquals(final byte[] actual, final byte[] expected,
			final String message) {
		if (expected == actual) {
			return;
		}
		if (null == expected) {
			if(haultonfailure)
			fail("expected a null array, but not null found. " + message);
			else{
				if(map==null)map=new LinkedHashMap<String, String>();
				map.put("expected a null array, but not null found. "+SEPARATOR + message+UtilityMethods.getRandomNumber()+System.currentTimeMillis(), actual+SEPARATOR+expected);
			}
		}
		if (null == actual) {
			if(haultonfailure)
				fail("expected not null array, but null found. " + message);
				else{
					if(map==null)map=new LinkedHashMap<String, String>();
					map.put("expected not null array, but null found. "+SEPARATOR + message+UtilityMethods.getRandomNumber()+System.currentTimeMillis(), actual+SEPARATOR+expected);
				}
		}

		assertEquals(actual.length, expected.length,
				"arrays don't have the same size. " + message);

		for (int i = 0; i < expected.length; i++) {
			if (expected[i] != actual[i]) {
				if(haultonfailure)
					fail("arrays differ firstly at element [" + i + "]; "+ "expected value is <" + expected[i] + "> but was <"+ actual[i] + ">. " + message);
					else{
						if(map==null)map=new LinkedHashMap<String, String>();
						map.put("arrays differ firstly at element [" + i + "]; "+ "expected value is <" + expected[i] + "> but was <"+ actual[i] + ">. " +message+SEPARATOR+UtilityMethods.getRandomNumber()+System.currentTimeMillis(), actual+SEPARATOR+expected);
					}
			}
		}
	}

	/**
	 * Asserts that two sets are equal.
	 */
	static public void assertEquals(Set<?> actual, Set<?> expected) {
		assertEquals(actual, expected, null);
	}

	/**
	 * Assert set equals
	 */
	static public void assertEquals(Set<?> actual, Set<?> expected,
			String message) {
		if (actual == expected) {
			return;
		}

		if (actual == null || expected == null) {
			// Keep the back compatible
			if (message == null)
				message="Sets not equal: expected: " + expected + " and actual: "+ actual;
			log.error(message(message, expected, actual));
			if (haultonfailure)
				fail(message);
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		if (!actual.equals(expected)) {
			if (message == null)message="Sets differ: expected " + expected + " but got " + actual;
			log.error(message(message, expected, actual));
			if (haultonfailure)
				fail(message);
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put(message + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}
	}

	/**
	 * Asserts that two maps are equal.
	 */
	static public void assertEquals(Map<?, ?> actual, Map<?, ?> expected) {
		if (actual == expected) {
			return;
		}

		if (actual == null || expected == null) {
			log.error(message(null, expected.toString(), actual.toString()));
			if (haultonfailure)
				fail("Maps not equal: expected: " + expected + " and actual: "
						+ actual);
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put("Maps not equal: " + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		if (actual.size() != expected.size()) {
			if (haultonfailure)
				fail("Maps do not have the same size:" + actual.size() + " != "
						+ expected.size());
			else {
				if (map == null) {
					map = new LinkedHashMap<String, String>();
				}
				map.put("Maps do not have the same size: " + SEPARATOR + UtilityMethods.getRandomNumber()+System.currentTimeMillis(),
						actual + SEPARATOR + expected);
			}
		}

		Set<?> entrySet = actual.entrySet();
		for (Iterator<?> iterator = entrySet.iterator(); iterator.hasNext();) {
			Map.Entry<?, ?> entry = (Map.Entry<?, ?>) iterator.next();
			Object key = entry.getKey();
			Object value = entry.getValue();
			Object expectedValue = expected.get(key);
			assertEquals(value, expectedValue, "Maps do not match for key:"
					+ key + " actual:" + value + " expected:" + expectedValue);
		}

	}

	static public void assertNotNull(Object object) {
		assertNotNull(object, null);
	}

	/**
	 * Asserts that an object isn't null. If it is, an AssertionFailedError,
	 * with the given message, is thrown.
	 * 
	 * @param object
	 *            the assertion object
	 * @param message
	 *            the assertion error message
	 */
	static public void assertNotNull(Object object, String message) {
		if (object == null) {
			String formatted = "";
			if (message != null) {
				formatted = message + " ";
			}
			log.error("assertNotNull : "+message);
			fail(formatted + "expected object to not be null");
		}
		assertTrue(object != null, message);
	}

	/**
	 * Asserts that an object is null. If it is not, an AssertionError, with the
	 * given message, is thrown.
	 * 
	 * @param object
	 *            the assertion object
	 */
	static private void assertNull(Object object) {
		assertNull(object, null);
	}

	/**
	 * Asserts that an object is null. If it is not, an AssertionFailedError,
	 * with the given message, is thrown.
	 * 
	 * @param object
	 *            the assertion object
	 * @param message
	 *            the assertion error message
	 */
	static private void assertNull(Object object, String message) {
		if (object != null) {
			if(haultonfailure)
			failNotSame(object, null, message);
			else{
				if(map==null)map=new LinkedHashMap<String, String>();
				map.put("expected a null array, but not null found. "+SEPARATOR + message+System.currentTimeMillis(), "null"+SEPARATOR+object);
			}
		}
	}

	/**
	 * Asserts that two objects refer to the same object. If they do not, an
	 * AssertionFailedError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static private void assertSame(Object actual, Object expected, String message) {
		if (expected == actual) {
			return;
		}
		failNotSame(actual, expected, message);
	}

	/**
	 * Asserts that two objects refer to the same object. If they do not, an
	 * AssertionError is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static private void assertSame(Object actual, Object expected) {
		assertSame(actual, expected, null);
	}

	/**
	 * Asserts that two objects do not refer to the same objects. If they do, an
	 * AssertionError, with the given message, is thrown.
	 * 
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message
	 *            the assertion error message
	 */
	static private void assertNotSame(Object actual, Object expected,
			String message) {
		if (expected == actual) {
			failSame(actual, expected, message);
		}
	}

	/**
	 * Asserts that two objects do not refer to the same object. If they do, an
	 * AssertionError is thrown.
	
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	static private void assertNotSame(Object actual, Object expected) {
		assertNotSame(actual, expected, null);
	}

	static private void failSame(Object actual, Object expected, String message) {
		String formatted = "";
		if (message != null) {
			formatted = message + " ";
		}
		fail(formatted + ASSERT_LEFT2 + expected + ASSERT_MIDDLE + actual
				+ ASSERT_RIGHT);
	}

	static private void failNotSame(Object actual, Object expected,
			String message) {
		String formatted = "";
		if (message != null) {
			formatted = message + " ";
		}
		fail(formatted + ASSERT_LEFT + expected + ASSERT_MIDDLE + actual
				+ ASSERT_RIGHT);
	}

	static private void failNotEquals(Object actual, Object expected, String message) {
		fail(format(actual, expected, message));
	}

	static String format(Object actual, Object expected, String message) {
		String formatted = "";
		if (null != message) {
			formatted = message + " ";
		}

		return formatted + ASSERT_LEFT + expected + ASSERT_MIDDLE + actual
				+ ASSERT_RIGHT;
	}

	private static void failAssertNoEqual(String defaultMessage, String message) {
		if (message != null) {
			fail(message);
		} else {
			fail(defaultMessage);
		}
	}

	/**
	 * Asserts that two values contain the same value. If
	 * they do not, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */

	// ///
	// assertNotEquals
	//

	public static void assertNotEquals(Object actual, Object expected,
			String message) {

		if ((expected == null) && (actual == null)) {
			log.error(message(message, expected.toString(), actual.toString()));
			failnotequals(actual, expected, message);
			return;
		} else if ((expected != null && actual == null)||(expected == null && actual != null)) {
			return;
		} else {
			if (expected.equals(actual)) {
//				assertArrayEquals(actual, expected, message);
				log.error(message(message, expected.toString(), actual.toString()));
				failnotequals(actual, expected, message);
				return;
			} else if (!expected.equals(actual)) {
				return;
			}
		}

		failnotequals(actual, expected, message);
	}

	
	/**
	 * Asserts that two Objects contain the different values. If
	 * they are same, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	
	public static void assertNotEquals(Object actual1, Object actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	
	/**
	 * Asserts that two Strings contain the different values. If
	 * they are same, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 * @param message 
	 * 			  the comment
	 */

	static void assertNotEquals(String actual1, String actual2, String message) {
		assertNotEquals((Object) actual1, (Object) actual2, message);
	}

	/**
	 * Asserts that two String contain the different values. If
	 * they are same, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	
	static void assertNotEquals(String actual1, String actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	
	/**
	 * Asserts that two long contain the different values. If
	 * they are same, an AssertionError is thrown.
	 *
	 * @param actual
	 *            the actual value
	 * @param expected
	 *            the expected value
	 */
	
	static void assertNotEquals(long actual1, long actual2, String message) {
		assertNotEquals(Long.valueOf(actual1), Long.valueOf(actual2), message);
	}

	static void assertNotEquals(long actual1, long actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	static void assertNotEquals(boolean actual1, boolean actual2, String message) {
		assertNotEquals(Boolean.valueOf(actual1), Boolean.valueOf(actual2),
				message);
	}

	static void assertNotEquals(boolean actual1, boolean actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	static void assertNotEquals(byte actual1, byte actual2, String message) {
		assertNotEquals(Byte.valueOf(actual1), Byte.valueOf(actual2), message);
	}

	static void assertNotEquals(byte actual1, byte actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	static void assertNotEquals(char actual1, char actual2, String message) {
		assertNotEquals(Character.valueOf(actual1), Character.valueOf(actual2),
				message);
	}

	static void assertNotEquals(char actual1, char actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	static void assertNotEquals(short actual1, short actual2, String message) {
		assertNotEquals(Short.valueOf(actual1), Short.valueOf(actual2), message);
	}

	static void assertNotEquals(short actual1, short actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	static void assertNotEquals(int actual1, int actual2, String message) {
		assertNotEquals(Integer.valueOf(actual1), Integer.valueOf(actual2),
				message);
	}

	static void assertNotEquals(int actual1, int actual2) {
		assertNotEquals(actual1, actual2, null);
	}

	static public void assertNotEquals(float actual1, float actual2,
			float delta, String message) {
		boolean fail = false;
		try {
			Assert.assertEquals(actual1, actual2, delta, message);
			fail = true;
		} catch (AssertionError e) {

		}

		if (fail) {
			Assert.fail(message);
		}
	}

	static public void assertNotEquals(float actual1, float actual2, float delta) {
		assertNotEquals(actual1, actual2, delta, null);
	}

	static public void assertNotEquals(double actual1, double actual2,
			double delta, String message) {
		boolean fail = false;
		try {
			Assert.assertEquals(actual1, actual2, delta, message);
			fail = true;
		} catch (AssertionError e) {

		}

		if (fail) {
			Assert.fail(message);
		}
	}

	static public void assertNotEquals(double actual1, double actual2,
			double delta) {
		assertNotEquals(actual1, actual2, delta, null);
	}

	public static void assertTrue(String actualErrormessage, String message) {
		
		
	}

	

	
}
