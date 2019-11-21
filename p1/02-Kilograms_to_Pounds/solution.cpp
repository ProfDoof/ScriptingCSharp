#include <iostream>
#include <iomanip>
using namespace std;

int main()
{
	double pounds = 0;
	double kilograms =0;
	double ounces = 0;
		
	cout << "This program converts kilograms to pounds and ounces." <<endl << "Kilograms: ";
	cin >> kilograms;
	
	//Convert kilograms to pounds
	pounds = kilograms * 2.2;
	
	//Calculate ounces
	ounces = (pounds - (int)pounds)*16;
	
	cout << endl << kilograms << " kilograms is " << (int)pounds << " pounds and " << fixed << setprecision(1) << ounces << " ounces." << endl;

}
