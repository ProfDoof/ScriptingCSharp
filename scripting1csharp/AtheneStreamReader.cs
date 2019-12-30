using System;
using System.IO;

/// <summary>
/// This class reflects all ReadLine output from stdin to stdout
/// </summary>
public class AtheneStreamReader: StreamReader
{
    public AtheneStreamReader(Stream stream): base(stream) {}

    /// <summary>
    /// This is an overload of the standard ReadLine that reflects all input back to output in the format
    /// <span class=input>{0}</span> where {0} is the input.
    /// </summary>
    /// <returns>The standard input (no real change from default ReadLine</returns>
    public override string ReadLine()
    {
        string line = base.ReadLine();
        System.Console.WriteLine("<span class=input>{0}</span>", line);
        return line;
    }
}
