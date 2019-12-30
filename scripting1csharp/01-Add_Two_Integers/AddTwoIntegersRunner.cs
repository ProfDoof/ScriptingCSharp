using System;
using System.IO;


class AddTwoIntegersRunner
{
    static void Main(string[] args)
    {
        // Replaces the standard console stream reader 
        // with my athene stream reader.
        Console.SetIn(new AtheneStreamReader(Console.OpenStandardInput()));

        // In order to use C# Reflection which allows me to invoke methods
        // even if they are private I need to create the students program as
        // an object.
        var program = new AddTwoIntegers.Program();

        // This uses C# reflection to get the Main method of the students
        // program. The 
        var runprogram = program
                            .GetType()
                            .GetMethod(
                                "Main", 
                                System.Reflection.BindingFlags.NonPublic | 
                                System.Reflection.BindingFlags.Static | 
                                System.Reflection.BindingFlags.Public
                            );

        // Now, as long as the students program has a main method this
        // next statement should be true. However, if it doesn't we should
        // probably throw an exception that says something meaningful to the
        // students.
        if (runprogram != null)
        {
            runprogram.Invoke(program, new object[] { args } );
        }

    }
}
