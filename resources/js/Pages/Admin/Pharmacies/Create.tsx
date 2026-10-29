import PharmacyForm from "./Form";

type Owner = {
    id: number;
    name: string;
    email: string;
};

export default function Create({ owners }: { owners: Owner[] }) {
    return <PharmacyForm owners={owners} />;
}
