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
	
	// Test if the three legs actually make a triangle
	// For a triangle to be formed by three lengths, the sum of any two
	// legs must be greater than the length of the remaining leg
	if ((a + b <= c) || (a + c <= b) || (b + c <= a))
		cout << "Those lengths do not form a triangle." << endl;
	
	//Test for equilateral triangle (all sides are equal)
	else if (a == b && b == c)
		cout << "This is an equilateral triangle." << endl;
	
	// Test for isosceles triangle (two sides are equal)
	else if (a == b || b == c || a == c)
		cout << "This is an isosceles triangle." << endl;
	

	// Test for right triangle
	// BUG: Doesn't test for all combinations of a, b, and c
	//	for pythagorean theorem
	else if (a*a + b*b == c*c)
		cout << "This is a right triangle." << endl;
	
	// Test for scalene triangle (no three sides are equal)
	// If the triangle is scalene, then determine whether it is an
	// obtuse scalene triangle or an acute scalene triangle
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

	
}
