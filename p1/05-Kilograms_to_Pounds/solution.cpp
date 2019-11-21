#include <iostream>
#include <iomanip>
using namespace std;

void convert(int kilograms, int& pounds, float& ounces)
{
	//Convert kilograms to pounds
	pounds = (int)(kilograms * 2.2);
	
	//Calculate ounces
	ounces = (kilograms*2.2 - pounds)*16;	
}

int main()
{
	int kilograms =0, pounds;
	float ounces;
	
	cout << "This program converts kilograms to pounds and ounces." <<endl << "Kilograms: ";
	cin >> kilograms;

	convert(kilograms,pounds,ounces);
	
	cout << endl << kilograms << " kilograms is " << pounds << " pounds and ";
	cout << fixed << setprecision(1) << ounces << " ounces." << endl;
}

