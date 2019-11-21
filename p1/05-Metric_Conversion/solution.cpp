#include <iostream>
using namespace std;

float convert(float feet, float inches)
{
    return ( (feet * 12 + inches) * 2.54 );
}

int main()
{
    float f, i;
    cout << "Feet? ";
    cin >> f;
    cout << "Inches? ";
    cin >> i;
    cout << "The length is " << convert(f,i) << " cm.\n";
}

