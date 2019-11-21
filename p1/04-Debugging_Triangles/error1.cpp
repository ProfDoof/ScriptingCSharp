/*
* Kyle Flanagan - 21 Debugging
* buggedcode.cpp
* This is bugged code for determining types of triangles
*/

#include <iostream>
using namespace std;

int main()
{
	// Give instructions
	cout << "This program determines types of triangles." << endl
	     << "Enter the lengths of each leg of the triangle" << endl
	     << "and the program will determine what type of triangle it is." << endl << endl;
	
	
	// Declare our variables
	float a;
	float b;
	float c;
	
	// Prompt for the lengths of the three legs of the triangle
	cout << "Length of leg a: ";
	cin >> a;
	cout << "Length of leg b: ";
	cin >> b;
	cout << "Length of leg c: ";
	cin >> c;
	
	// Test for isosceles triangle (two sides are equal)
	// BUG: This will say an equilateral triangle is an isosceles triangle (which, in all
	// 	technicality is true, but not for our case)
	if (a == b || b == c || a == c)
		cout << "This is an isosceles triangle." << endl;
	

	//Test for equilateral triangle (all sides are equal)
	//BUG: No "else if"
	else if (a == b && b == c)
		cout << "This is an equilateral triangle." << endl;
	
	// Test for scalene triangle (no three sides are equal)
	// If the triangle is scalene, then determine whether it is an
	// obtuse scalene triangle or an acute scalene triangle
	// BUG: This will say some right triangles are scalene, which, is also true in technicality
	// 	but not for our case
	if (a != b && b != c && a != c)
	{
		// Test for obtuse triangle
		// A triangle is obtuse if any of the following conditions are true
		// a*a + b*b < c*c
		// b*b + c*c < a*a
		// a*a + c*c < b*b
		if((a*a + b*b < c*c) || (b*b + c*c < a*a) || (a*a + c*c < b*b))
			cout << "This is an obtuse scalene triangle." << endl;
		else
			cout << "This is an acute scalene triangle." << endl;
	}
	
	// Test for right triangle
	// BUG: This only tests for leg c being the hypotenuse, if the user were to
	// 	enter 5, 4, 3 then program wouldn't classify it as a right triangle
	//	also, the lack of "else if" will display that the triangle is a right
	//	triangle even though it has probably been classified as a scalene triangle
	if (a*a + b*b == c*c)
		cout << "This is a right triangle." << endl;
	
	// Test if the three legs actually make a triangle
	// For a triangle to be formed by three lengths, the sum of any two
	// legs must be greater than the length of the remaining leg
	// BUG: This should be the very first test, because the cases prior to this may
	// 	be true, but the legs may not actually form a triangle. Also, there
	//	is a bug in the logic, the <'s should be <='s 
	else if ((a + b < c) || (a + c < b) || (b + c < a))
		cout << "Those lengths do not form a triangle." << endl;
}
