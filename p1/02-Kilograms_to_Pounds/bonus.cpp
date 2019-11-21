#include<iostream>
#include<iomanip>
using namespace std;

int main()
{
	int pounds = 0;
	double kilograms =0;
	double ounces = 0;
		
	cout << "This program converts kilograms to pounds and ounces.";

    cout << endl << "Kilograms: ";
	cin >> kilograms;
	
	//Convert kilograms to pounds
	pounds = static_cast<int>(kilograms * 2.2);
	
	//Calculate ounces
	ounces = (kilograms * 2.2 - pounds)*16;
	
	if (kilograms == 1)
        cout << endl << kilograms << " kilogram is ";
    else
        cout << endl << kilograms << " kilograms is ";
    if (pounds == 1)
        cout << pounds << " pound and ";
    else
        cout << pounds << " pounds and ";
    cout << fixed << setprecision(1) << ounces << " ounces." << endl;
}
