export default function GynaeHistoryForm({ data, setData }) {
    return (
        <form className="space-y-4">
            <fieldset className="mb-4 border border-solid border-gray-300 p-3">
                <legend>Add patient's gynaecological history</legend>

                {/* Marriage Details */}
                <div className="mb-4">
                    <h4 className="mb-2 font-semibold text-lime-700">
                        Marriage Details
                    </h4>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block">Marital Status</label>
                            <select
                                value={
                                    data?.gynae_history?.marital_status || ""
                                }
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        marital_status: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            >
                                <option value="">Select</option>
                                <option value="Married">Married</option>
                                <option value="Unmarried">Unmarried</option>
                                <option value="Widow">Widow</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div>
                            <label className="block">Marriage Duration</label>
                            <input
                                type="text"
                                value={
                                    data?.gynae_history?.marriage_duration || ""
                                }
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        marriage_duration: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                placeholder="e.g. 5 years"
                            />
                        </div>
                        <div>
                            <label className="block">Consanguinity</label>
                            <select
                                value={data?.gynae_history?.consanguinity || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        consanguinity: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            >
                                <option value="">Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Menstrual History */}
                <div className="mb-4">
                    <h4 className="mb-2 font-semibold text-lime-700">
                        Menstrual History
                    </h4>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block">Menarche Age</label>
                            <input
                                type="text"
                                value={data?.gynae_history?.menarche_age || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        menarche_age: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            />
                        </div>
                        <div>
                            <label className="block">
                                Last Menstrual Period (LMP)
                            </label>
                            <input
                                type="date"
                                value={data?.gynae_history?.lmp || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        lmp: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            />
                        </div>
                        <div>
                            <label className="block">Cycle</label>
                            <input
                                type="text"
                                value={data?.gynae_history?.cycle || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        cycle: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                placeholder="e.g. 28 days"
                            />
                        </div>
                        <div>
                            <label className="block">Flow</label>
                            <input
                                type="text"
                                value={data?.gynae_history?.flow || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        flow: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                placeholder="Normal/Heavy"
                            />
                        </div>
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                checked={
                                    data?.gynae_history?.dysmenorrhea || false
                                }
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        dysmenorrhea: e.target.checked,
                                    })
                                }
                                className="mr-2"
                            />
                            <label className="block">Dysmenorrhea</label>
                        </div>
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                checked={
                                    data?.gynae_history?.contraceptive_use ||
                                    false
                                }
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        contraceptive_use: e.target.checked,
                                    })
                                }
                                className="mr-2"
                            />
                            <label className="block">Contraceptive Use</label>
                        </div>
                    </div>
                </div>

                {/* Obstetrical History */}
                <div className="mb-4">
                    <h4 className="mb-2 font-semibold text-lime-700">
                        Obstetrical History
                    </h4>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block">Gravida</label>
                            <input
                                type="number"
                                value={data?.gynae_history?.gravida || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        gravida: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            />
                        </div>
                        <div>
                            <label className="block">Para</label>
                            <input
                                type="number"
                                value={data?.gynae_history?.para || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        para: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            />
                        </div>
                        <div>
                            <label className="block">Abortion</label>
                            <input
                                type="number"
                                value={data?.gynae_history?.abortion || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        abortion: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            />
                        </div>
                        <div>
                            <label className="block">Living Children</label>
                            <input
                                type="number"
                                value={
                                    data?.gynae_history?.living_children || ""
                                }
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        living_children: e.target.value,
                                    })
                                }
                                className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                            />
                        </div>
                    </div>
                </div>

                {/* Current Pregnancy */}
                <div>
                    <h3 className="mb-2 text-lg font-semibold text-lime-700">
                        Current Pregnancy
                    </h3>
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="text-sm font-medium">EDD</label>
                            <input
                                type="date"
                                value={data?.gynae_history?.edd || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        edd: e.target.value,
                                    })
                                }
                                className="w-full rounded border p-2"
                            />
                        </div>
                        <div>
                            <label className="text-sm font-medium">
                                Antenatal Checkups
                            </label>
                            <select
                                value={data?.gynae_history?.anc || ""}
                                onChange={(e) =>
                                    setData("gynae_history", {
                                        ...data.gynae_history,
                                        anc: e.target.value,
                                    })
                                }
                                className="w-full rounded border p-2"
                            >
                                <option value="">Select</option>
                                <option value="Regular">Regular</option>
                                <option value="Irregular">Irregular</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Other History */}
                <div className="mt-4">
                    <label className="block">Other Notes</label>
                    <textarea
                        value={data?.gynae_history?.other_history || ""}
                        onChange={(e) =>
                            setData("gynae_history", {
                                ...data.gynae_history,
                                other_history: e.target.value,
                            })
                        }
                        className="focus:ring-opacity-50 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                        rows="3"
                    ></textarea>
                </div>
            </fieldset>
        </form>
    );
}
